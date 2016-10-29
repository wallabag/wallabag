# config valid only for current version of Capistrano

set :application, 'wallabag'
set :repo_url, 'git@github.com:wallabag/wallabag.git'

set :ssh_user, 'framasoft_bag'
server '78.46.248.87', user: fetch(:ssh_user), roles: %w{web app db}

set :format, :pretty
set :log_level, :info
# set :log_level, :debug

set :composer_install_flags, '--no-dev --prefer-dist --no-interaction --optimize-autoloader'

set :linked_files, %w{app/config/parameters.yml}
set :linked_dirs, [fetch(:log_path), "var/sessions", "web/uploads", "data"]
set :keep_releases, 3

after 'deploy:updated', 'symfony:cache:clear'
