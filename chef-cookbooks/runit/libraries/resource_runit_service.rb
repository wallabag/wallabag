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

require 'chef/resource'
require 'chef/resource/service'

class Chef
  class Resource
    class RunitService < Chef::Resource::Service

      def initialize(name, run_context=nil)
        super
        runit_node = runit_attributes_from_node(run_context)
        @resource_name = :runit_service
        @provider = Chef::Provider::Service::Runit
        @supports = { :restart => true, :reload => true, :status => true }
        @action = :enable
        @allowed_actions = [:nothing, :start, :stop, :enable, :disable, :restart, :reload, :status, :once, :hup, :cont, :term, :kill, :up, :down, :usr1, :usr2]

        # sv_bin, sv_dir, service_dir and lsb_init_dir may have been set in the
        # node attributes
        @sv_bin = runit_node[:sv_bin] || '/usr/bin/sv'
        @sv_dir = runit_node[:sv_dir] || '/etc/sv'
        @service_dir = runit_node[:service_dir] || '/etc/service'
        @lsb_init_dir = runit_node[:lsb_init_dir] || '/etc/init.d'

        @control = []
        @options = {}
        @env = {}
        @log = true
        @cookbook = nil
        @finish = false
        @owner = nil
        @group = nil
        @enabled = false
        @running = false
        @default_logger = false
        @restart_on_update = true
        @run_template_name = @service_name
        @log_template_name = @service_name
        @finish_script_template_name = @service_name
        @control_template_names = {}
        @status_command = "#{@sv_bin} status #{@service_dir}"
        @sv_templates = true
        @log_size = nil
        @log_num = nil
        @log_min = nil
        @log_timeout = nil
        @log_processor = nil
        @log_socket = nil
        @log_prefix = nil
        @log_config_append = nil

        #
        # Backward Compat Hack
        #
        # This ensures a 'service' resource exists for all 'runit_service' resources.
        # This should allow all recipes using the previous 'runit_service' definition to
        # continue operating.
        #
        unless run_context.nil?
          service_dir_name = ::File.join(@service_dir, @name)
          @service_mirror = Chef::Resource::Service.new(name, run_context)
          @service_mirror.provider(Chef::Provider::Service::Simple)
          @service_mirror.supports(@supports)
          @service_mirror.start_command("#{@sv_bin} start #{service_dir_name}")
          @service_mirror.stop_command("#{@sv_bin} stop #{service_dir_name}")
          @service_mirror.restart_command("#{@sv_bin} restart #{service_dir_name}")
          @service_mirror.status_command("#{@sv_bin} status #{service_dir_name}")
          @service_mirror.action(:nothing)
          run_context.resource_collection.insert(@service_mirror)
        end
      end

      def sv_bin(arg=nil)
        set_or_return(:sv_bin, arg, :kind_of => [String])
      end

      def sv_dir(arg=nil)
        set_or_return(:sv_dir, arg, :kind_of => [String, FalseClass])
      end

      def service_dir(arg=nil)
        set_or_return(:service_dir, arg, :kind_of => [String])
      end

      def lsb_init_dir(arg=nil)
        set_or_return(:lsb_init_dir, arg, :kind_of => [String])
      end

      def control(arg=nil)
        set_or_return(:control, arg, :kind_of => [Array])
      end

      def options(arg=nil)
        if @env.empty?
          opts = @options
        else
          opts = @options.merge!(:env_dir => ::File.join(@sv_dir, @service_name, 'env'))
        end
        set_or_return(
          :options,
          arg,
          :kind_of => [Hash],
          :default => opts
        )
      end

      def env(arg=nil)
        set_or_return(:env, arg, :kind_of => [Hash])
      end

      def log(arg=nil)
        set_or_return(:log, arg, :kind_of => [TrueClass, FalseClass])
      end

      def cookbook(arg=nil)
        set_or_return(:cookbook, arg, :kind_of => [String])
      end

      def finish(arg=nil)
        set_or_return(:finish, arg, :kind_of => [TrueClass, FalseClass])
      end

      def owner(arg=nil)
        set_or_return(:owner, arg, :regex => [Chef::Config[:user_valid_regex]])
      end

      def group(arg=nil)
        set_or_return(:group, arg, :regex => [Chef::Config[:group_valid_regex]])
      end

      def default_logger(arg=nil)
        set_or_return(:default_logger, arg, :kind_of => [TrueClass, FalseClass])
      end

      def restart_on_update(arg=nil)
        set_or_return(:restart_on_update, arg, :kind_of => [TrueClass, FalseClass])
      end

      def run_template_name(arg=nil)
        set_or_return(:run_template_name, arg, :kind_of => [String])
      end
      alias :template_name :run_template_name

      def log_template_name(arg=nil)
        set_or_return(:log_template_name, arg, :kind_of => [String])
      end

      def finish_script_template_name(arg=nil)
        set_or_return(:finish_script_template_name, arg, :kind_of => [String])
      end

      def control_template_names(arg=nil)
        set_or_return(
          :control_template_names,
          arg,
          :kind_of => [Hash],
          :default => set_control_template_names
        )
      end

      def set_control_template_names
        @control.each do |signal|
          @control_template_names[signal] ||= @service_name
        end
        @control_template_names
      end

      def sv_templates(arg=nil)
        set_or_return(:sv_templates, arg, :kind_of => [TrueClass, FalseClass])
      end

      def log_size(arg=nil)
        set_or_return(:log_size, arg, :kind_of => [Integer])
      end

      def log_num(arg=nil)
        set_or_return(:log_num, arg, :kind_of => [Integer])
      end

      def log_min(arg=nil)
        set_or_return(:log_min, arg, :kind_of => [Integer])
      end

      def log_timeout(arg=nil)
        set_or_return(:log_timeout, arg, :kind_of => [Integer])
      end

      def log_processor(arg=nil)
        set_or_return(:log_processor, arg, :kind_of => [String])
      end

      def log_socket(arg=nil)
        set_or_return(:log_socket, arg, :kind_of => [String, Hash])
      end

      def log_prefix(arg=nil)
        set_or_return(:log_prefix, arg, :kind_of => [String])
      end

      def log_config_append(arg=nil)
        set_or_return(:log_config_append, arg, :kind_of => [String])
      end

      def runit_attributes_from_node(run_context)
        runit_attr = if run_context && run_context.node
          run_context.node[:runit]
        end
        runit_attr || {}
      end
    end
  end
end
