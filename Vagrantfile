# -*- mode: ruby -*-
# vi: set ft=ruby :
Vagrant.require_version ">= 1.8.6"

Vagrant.configure(2) do |config|
  config.vm.box = "ubuntu/xenial64"

  # Display the VirtualBox GUI when booting the machine
  config.vm.provider "virtualbox" do |vb|
    # vb.gui = true
    vb.name = "adventofcode"
    vb.memory = 1024
  end
end
