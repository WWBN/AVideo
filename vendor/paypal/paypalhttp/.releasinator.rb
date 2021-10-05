require 'securerandom'

#### releasinator config ####
configatron.product_name = "PayPalHttp-PHP"

# List of items to confirm from the person releasing.  Required, but empty list is ok.
configatron.prerelease_checklist_items = [
  "Sanity check the master branch."
]

def test
  CommandProcessor.command("composer update", live_output=true)
  CommandProcessor.command("vendor/bin/phpunit", live_output=true)
end

configatron.custom_validation_methods = [
  method(:test)
]

# there are no separate build steps for PayPalHttp-PHP, so it is just empty method
def build_method
end

# The command that builds the sdk.  Required.
configatron.build_method = method(:build_method)

# Creating and pushing a tag will automatically create a release, so it is just empty method
def publish_to_package_manager(version)
end

# The method that publishes the sdk to the package manager.  Required.
configatron.publish_to_package_manager_method = method(:publish_to_package_manager)

def wait_for_package_manager(version)
end

# Version is tied to the current tag, noop
def update_version_method(version)
end

# The method that waits for the package manager to be done.  Required
configatron.wait_for_package_manager_method = method(:wait_for_package_manager)
configatron.update_version_method = method(:update_version_method)

# Whether to publish the root repo to GitHub.  Required.
configatron.release_to_github = true

