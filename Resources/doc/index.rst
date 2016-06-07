A bundle to add maintenance mode to Symfony project.

Installation
Step 1: Download the Bundle

Open a command console, enter your project directory and execute the
following command to download the latest stable version of this bundle:

$ composer require <package-name> "~1"
This command requires you to have Composer installed globally, as explained
in the installation chapter
of the Composer documentation.

Step 2: Enable the Bundle

Then, enable the bundle by adding it to the list of registered bundles
in the app/AppKernel.php file of your project:

<?php
// app/AppKernel.php

// ...
class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
            // ...

            new Efi\MaintenanceBundle\EfiMaintenanceBundle()(),
        );

        // ...
    }

    // ...
}
Step 3: Add Routing

Add below code to app/config/routing.yml

efi_maintenance:
    resource: "@EfiMaintenanceBundle/Resources/config/routing.yml"
Step 4: Configuration

Default configuration is:

efi_maintenance:
    maintenance_mode:     false

    # After due-date maintenance mode will not invoke anymore. Date format should be 'YYYY-MM-DD' or 'YYYY-MM-DD HH:MM:SS' or 'YYYY-MM-DD HH:MM:SS +/-TT:TT' or timestamp
    due_date:             null # Example: 2016-7-6 or 2016-7-6 10:10 or 2016-7-6 10:10:10 +02:00 or 1467763200

    # View parameters will set on default twig template of maintenance bundle. These values translate before rendering
    view:
        title:                efi.maintenance.messages.under_construction.title
        description:          efi.maintenance.messages.under_construction.description

    # If maintenance mode is false or it is after due date then it will redirect to below path or url by requesting maintenance page.
    redirect_on_normal:
        available:            true
        redirect_url:         /
        redirect_route:       null

    # While the website is in maintenance mode it is possible to allow some users to visit the website based on users' roles or usernames.
    authorized_users:
        roles:

            # Defaults:
            - ROLE_ADMIN
            - ROLE_SUPER_ADMIN
        usernames:            []

    # You may like exclude some pages from maintenance mode. Here you can define their paths or routes.
    authorized_areas:
        paths:

            # Default:
            - /login
        routes:               []