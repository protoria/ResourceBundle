Resource Bundle
========================
Installation
------------

Add the bundle to your `composer.json`:

    composer require igdr/resource-bundle

and run:

    php composer.phar update

Then add the ResourceBundle to your application kernel:

    // app/IgdrKernel.php
    public function registerBundles()
    {
        return array(
            // ...
            new Igdr\Bundle\ResourceBundle\IgdrResourceBundle(),
            // ...
        );
    }

Add configuration to config.yml

    igdr_resource:
        controller:
            index:
                template: "@AppBase/Admin/Abstract/index.html.twig"           
            edit:
                template: "@AppBase/Admin/Abstract/edit.html.twig"