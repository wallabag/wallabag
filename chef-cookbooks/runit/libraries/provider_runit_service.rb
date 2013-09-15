#
# Cookbook Name:: runit
# Provider:: service
#
# Copyright 2011, Joshua Timberman
# Copyright 2011, Opscode, Inc.
#
# Licensed under the Apache License, Version 2.0 (the "License");
# you may not use this file except in compliance with the License.
# You may obtain a copy of the License at
#
#     http://www.apache.org/licenses/LICENSE-2.0
#
# Unless required by applicable law or agreed to in writing, software
# distributed under the License is distributed on an "AS IS" BASIS,
# WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
# See the License for the specific language governing permissions and
# limitations under the License.
#

require 'chef/provider/service'
require 'chef/provider/link'
require 'chef/resource/link'
require 'chef/provider/directory'
require 'chef/resource/directory'
require 'chef/provider/template'
require 'chef/resource/template'
require 'chef/provider/file'
require 'chef/resource/file'
require 'chef/mixin/shell_out'
require 'chef/mixin/language'

class Chef
  class Provider
    class Service
      class Runit < Chef::Provider::Service
        include Chef::Mixin::ShellOut

        def initialize(*args)
          super
          @sv_dir = nil
          @run_script = nil
          @log_dir = nil
          @log_main_dir = nil
          @default_log_dir = nil
          @log_run_script = nil
          @log_config_file = nil
          @env_dir = nil
          @env_files = nil
          @finish_script = nil
          @control_dir = nil
          @control_signal_files = nil
          @lsb_init = nil
          @service_link = nil
          @new_resource.supports[:status] = true
        end

        def load_current_resource
          @current_resource = Chef::Resource::RunitService.new(new_resource.name)
          @current_resource.service_name(new_resource.service_name)

          Chef::Log.debug("Checking status of service #{new_resource.service_name}")

          # verify Runit was installed properly
          unless ::File.exist?(new_resource.sv_bin) && ::File.executable?(new_resource.sv_bin)
            no_runit_message = "Could not locate main runit sv_bin at \"#{new_resource.sv_bin}\". "
            no_runit_message << "Did you remember to install runit before declaring a \"runit_service\" resource? "
            no_runit_message << "\n\nTry adding the following to the top of your recipe:\n\ninclude_recipe \"runit\""
            raise no_runit_message
          end

          @current_resource.running(running?)
          @current_resource.enabled(enabled?)
          @current_resource
        end

        #
        # Chef::Provider::Service overrides
        #

        def action_enable
          converge_by("configure service #{@new_resource}") do
            configure_service # Do this every run, even if service is already enabled and running
            Chef::Log.info("#{@new_resource} configured")
          end
          if @current_resource.enabled
            Chef::Log.debug("#{@new_resource} already enabled - nothing to do")
          else
            converge_by("enable service #{@new_resource}") do
              enable_service
              Chef::Log.info("#{@new_resource} enabled")
            end
          end
          load_new_resource_state
          @new_resource.enabled(true)
          restart_service if @new_resource.restart_on_update and run_script.updated_by_last_action?
          restart_log_service if @new_resource.restart_on_update and log_run_script.updated_by_last_action?
          restart_log_service if @new_resource.restart_on_update and log_config_file.updated_by_last_action?
        end

        def configure_service
          if new_resource.sv_templates
            Chef::Log.debug("Creating sv_dir for #{new_resource.service_name}")
            sv_dir.run_action(:create)
            Chef::Log.debug("Creating run_script for #{new_resource.service_name}")
            run_script.run_action(:create)

            if new_resource.log
              Chef::Log.debug("Setting up svlog for #{new_resource.service_name}")
              log_dir.run_action(:create)
              log_main_dir.run_action(:create)
              default_log_dir.run_action(:create) if new_resource.default_logger
              log_run_script.run_action(:create)
              log_config_file.run_action(:create)
            else
              Chef::Log.debug("log not specified for #{new_resource.service_name}, continuing")
            end

            unless new_resource.env.empty?
              Chef::Log.debug("Setting up environment files for #{new_resource.service_name}")
              env_dir.run_action(:create)
              env_files.each {|file| file.run_action(:create)}
            else
              Chef::Log.debug("Environment not specified for #{new_resource.service_name}, continuing")
            end

            if new_resource.finish
              Chef::Log.debug("Creating finish script for #{new_resource.service_name}")
              finish_script.run_action(:create)
            else
              Chef::Log.debug("Finish script not specified for #{new_resource.service_name}, continuing")
            end

            unless new_resource.control.empty?
              Chef::Log.debug("Creating control signal scripts for #{new_resource.service_name}")
              control_dir.run_action(:create)
              control_signal_files.each {|file| file.run_action(:create)}
            else
              Chef::Log.debug("Control signals not specified for #{new_resource.service_name}, continuing")
            end
          end

          Chef::Log.debug("Creating lsb_init compatible interface #{new_resource.service_name}")
          lsb_init.run_action(:create)
        end

        def enable_service
          Chef::Log.debug("Creating symlink in service_dir for #{new_resource.service_name}")
          service_link.run_action(:create)

          Chef::Log.debug("waiting until named pipe #{service_dir_name}/supervise/ok exists.")
          until ::FileTest.pipe?("#{service_dir_name}/supervise/ok") do
            sleep 1
            Chef::Log.debug(".")
          end

          if new_resource.log
            Chef::Log.debug("waiting until named pipe #{service_dir_name}/log/supervise/ok exists.")
            until ::FileTest.pipe?("#{service_dir_name}/log/supervise/ok") do
              sleep 1
              Chef::Log.debug(".")
            end
          end
        end

        def disable_service
          shell_out("#{new_resource.sv_bin} down #{service_dir_name}")
          Chef::Log.debug("#{new_resource} down")
          FileUtils.rm(service_dir_name)
          Chef::Log.debug("#{new_resource} service symlink removed")
        end

        def start_service
          shell_out!("#{new_resource.sv_bin} start #{service_dir_name}")
        end

        def stop_service
          shell_out!("#{new_resource.sv_bin} stop #{service_dir_name}")
        end

        def restart_service
          shell_out!("#{new_resource.sv_bin} restart #{service_dir_name}")
        end

        def restart_log_service
          shell_out!("#{new_resource.sv_bin} restart #{service_dir_name}/log")
        end

        def reload_service
          shell_out!("#{new_resource.sv_bin} force-reload #{service_dir_name}")
        end

        def reload_log_service
          shell_out!("#{new_resource.sv_bin} force-reload #{service_dir_name}/log")
        end

        #
        # Addtional Runit-only actions
        #

        # only take action if the service is running
        [:down, :hup, :int, :term, :kill, :quit].each do |signal|
          define_method "action_#{signal}".to_sym do
            if @current_resource.running
              runit_send_signal(signal)
            else
              Chef::Log.debug("#{new_resource} not running - nothing to do")
            end
          end
        end

        # only take action if service is *not* running
        [:up, :once, :cont].each do |signal|
          define_method "action_#{signal}".to_sym do
            if @current_resource.running
              Chef::Log.debug("#{new_resource} already running - nothing to do")
            else
              runit_send_signal(signal)
            end
          end
        end

        def action_usr1
          runit_send_signal(1, :usr1)
        end

        def action_usr2
          runit_send_signal(2, :usr2)
        end

        private

        def runit_send_signal(signal, friendly_name=nil)
          friendly_name ||= signal
          converge_by("send #{friendly_name} to #{new_resource}") do
            shell_out!("#{new_resource.sv_bin} #{signal} #{service_dir_name}")
            Chef::Log.info("#{new_resource} sent #{friendly_name}")
            new_resource.updated_by_last_action(true)
          end
        end

        def running?
          cmd = shell_out("#{new_resource.sv_bin} status #{new_resource.service_name}")
          (cmd.stdout =~ /^run:/ && cmd.exitstatus == 0)
        end

        def log_running?
          cmd = shell_out("#{new_resource.sv_bin} status #{new_resource.service_name}/log")
          (cmd.stdout =~ /^run:/ && cmd.exitstatus == 0)
        end

        def enabled?
          ::File.exists?(::File.join(service_dir_name, "run"))
        end

        def log_service_name
          ::File.join(new_resource.service_name, "log")
        end

        def sv_dir_name
          ::File.join(new_resource.sv_dir, new_resource.service_name)
        end

        def service_dir_name
          ::File.join(new_resource.service_dir, new_resource.service_name)
        end

        def log_dir_name
          ::File.join(new_resource.service_dir, new_resource.service_name, log)
        end

        def template_cookbook
          new_resource.cookbook.nil? ? new_resource.cookbook_name.to_s : new_resource.cookbook
        end

        def default_logger_content
          return <<-EOF
#!/bin/sh
exec svlogd -tt /var/log/#{new_resource.service_name}
EOF
        end

        #
        # Helper Resources
        #
        def sv_dir
          return @sv_dir unless @sv_dir.nil?
          @sv_dir = Chef::Resource::Directory.new(sv_dir_name, run_context)
          @sv_dir.recursive(true)
          @sv_dir.owner(new_resource.owner)
          @sv_dir.group(new_resource.group)
          @sv_dir.mode(00755)
          @sv_dir
        end

        def run_script
          return @run_script unless @run_script.nil?
          @run_script = Chef::Resource::Template.new(::File.join(sv_dir_name, 'run'), run_context)
          @run_script.owner(new_resource.owner)
          @run_script.group(new_resource.group)
          @run_script.source("sv-#{new_resource.run_template_name}-run.erb")
          @run_script.cookbook(template_cookbook)
          @run_script.mode(00755)
          if new_resource.options.respond_to?(:has_key?)
            @run_script.variables(:options => new_resource.options)
          end
          @run_script
        end

        def log_dir
          return @log_dir unless @log_dir.nil?
          @log_dir = Chef::Resource::Directory.new(::File.join(sv_dir_name, 'log'), run_context)
          @log_dir.recursive(true)
          @log_dir.owner(new_resource.owner)
          @log_dir.group(new_resource.group)
          @log_dir.mode(00755)
          @log_dir
        end

        def log_main_dir
          return @log_main_dir unless @log_main_dir.nil?
          @log_main_dir = Chef::Resource::Directory.new(::File.join(sv_dir_name, 'log', 'main'), run_context)
          @log_main_dir.recursive(true)
          @log_main_dir.owner(new_resource.owner)
          @log_main_dir.group(new_resource.group)
          @log_main_dir.mode(00755)
          @log_main_dir
        end

        def default_log_dir
          return @default_log_dir unless @default_log_dir.nil?
          @default_log_dir = Chef::Resource::Directory.new(::File.join("/var/log/#{new_resource.service_name}"), run_context)
          @default_log_dir.recursive(true)
          @default_log_dir.owner(new_resource.owner)
          @default_log_dir.group(new_resource.group)
          @default_log_dir.mode(00755)
          @default_log_dir
        end

        def log_run_script
          return @log_run_script unless @log_run_script.nil?
          if new_resource.default_logger
            @log_run_script = Chef::Resource::File.new(::File.join( sv_dir_name,
                                                                    'log',
                                                                    'run' ),
                                                       run_context)
            @log_run_script.content(default_logger_content)
            @log_run_script.owner(new_resource.owner)
            @log_run_script.group(new_resource.group)
            @log_run_script.mode(00755)
          else
            @log_run_script = Chef::Resource::Template.new(::File.join( sv_dir_name,
                                                                        'log',
                                                                        'run' ),
                                                            run_context)
            @log_run_script.owner(new_resource.owner)
            @log_run_script.group(new_resource.group)
            @log_run_script.mode(00755)
            @log_run_script.source("sv-#{new_resource.log_template_name}-log-run.erb")
            @log_run_script.cookbook(template_cookbook)
            if new_resource.options.respond_to?(:has_key?)
              @log_run_script.variables(:options => new_resource.options)
            end
          end
          @log_run_script
        end

        def log_config_file
          return @log_config_file unless @log_config_file.nil?
          @log_config_file = Chef::Resource::Template.new(::File.join(sv_dir_name, 'log', 'config'), run_context)
          @log_config_file.owner(new_resource.owner)
          @log_config_file.group(new_resource.group)
          @log_config_file.mode(00644)
          @log_config_file.cookbook("runit")
          @log_config_file.source("log-config.erb")
          @log_config_file.variables({
            :size => new_resource.log_size,
            :num => new_resource.log_num,
            :min => new_resource.log_min,
            :timeout => new_resource.log_timeout,
            :processor => new_resource.log_processor,
            :socket => new_resource.log_socket,
            :prefix => new_resource.log_prefix,
            :append => new_resource.log_config_append
          })
          @log_config_file
        end

        def env_dir
          return @env_dir unless @env_dir.nil?
          @env_dir = Chef::Resource::Directory.new(::File.join(sv_dir_name, 'env'), run_context)
          @env_dir.owner(new_resource.owner)
          @env_dir.group(new_resource.group)
          @env_dir.mode(00755)
          @env_dir
        end

        def env_files
          return @env_files unless @env_files.nil?
          @env_files = new_resource.env.map do |var, value|
            env_file = Chef::Resource::File.new(::File.join(sv_dir_name, 'env', var), run_context)
            env_file.owner(new_resource.owner)
            env_file.group(new_resource.group)
            env_file.content(value)
            env_file
          end
          @env_files
        end

        def finish_script
          return @finish_script unless @finish_script.nil?
          @finish_script = Chef::Resource::Template.new(::File.join(sv_dir_name, 'finish'), run_context)
          @finish_script.owner(new_resource.owner)
          @finish_script.group(new_resource.group)
          @finish_script.mode(00755)
          @finish_script.source("sv-#{new_resource.finish_script_template_name}-finish.erb")
          @finish_script.cookbook(template_cookbook)
          if new_resource.options.respond_to?(:has_key?)
            @finish_script.variables(:options => new_resource.options)
          end
          @finish_script
        end

        def control_dir
          return @control_dir unless @control_dir.nil?
          @control_dir = Chef::Resource::Directory.new(::File.join(sv_dir_name, 'control'), run_context)
          @control_dir.owner(new_resource.owner)
          @control_dir.group(new_resource.group)
          @control_dir.mode(00755)
          @control_dir
        end

        def control_signal_files
          return @control_signal_files unless @control_signal_files.nil?
          @control_signal_files = new_resource.control.map do |signal|
            control_signal_file = Chef::Resource::Template.new(::File.join( sv_dir_name,
                                                                            'control',
                                                                            signal),
                                                                run_context)
            control_signal_file.owner(new_resource.owner)
            control_signal_file.group(new_resource.group)
            control_signal_file.mode(00755)
            control_signal_file.source("sv-#{new_resource.control_template_names[signal]}-#{signal}.erb")
            control_signal_file.cookbook(template_cookbook)
            if new_resource.options.respond_to?(:has_key?)
              control_signal_file.variables(:options => new_resource.options)
            end
            control_signal_file
          end
          @control_signal_files
        end

        def lsb_init
          return @lsb_init unless @lsb_init.nil?
          initfile = ::File.join(new_resource.lsb_init_dir, new_resource.service_name)
          if node['platform'] == 'debian'
            ::File.unlink(initfile) if ::File.symlink?(initfile)
            @lsb_init = Chef::Resource::Template.new(initfile, run_context)
            @lsb_init.owner('root')
            @lsb_init.group('root')
            @lsb_init.mode(00755)
            @lsb_init.cookbook('runit')
            @lsb_init.source('init.d.erb')
            @lsb_init.variables(:name => new_resource.service_name)
          else
            @lsb_init = Chef::Resource::Link.new(initfile, run_context)
            @lsb_init.to(new_resource.sv_bin)
          end
          @lsb_init
        end

        def service_link
          return @service_link unless @service_link.nil?
          @service_link = Chef::Resource::Link.new(::File.join(service_dir_name), run_context)
          @service_link.to(sv_dir_name)
          @service_link
        end
      end
    end
  end
end
