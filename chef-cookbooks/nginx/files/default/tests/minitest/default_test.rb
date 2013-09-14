require File.expand_path('../support/helpers', __FILE__)

describe 'nginx::default' do
  include Helpers::Nginx
  it 'installs nginx' do
    package("nginx").must_be_installed
  end

  it 'runs a service named nginx' do
    service("nginx").must_be_running
  end
end
