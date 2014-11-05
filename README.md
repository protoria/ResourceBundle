Resource Bundle
========================
Installation
------------

Add the bundle to your `composer.json`:

    "igdr/resource-bundle" : "dev-master"

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
        namespace:
            form: "App\Bundle\{Bundle}Bundle\Form\Type\Admin\{Entity}Type"   #Namespace mask for form types
            grid: "App\Bundle\{Bundle}Bundle\Grid\Type\Admin\{Entity}Type"   #Namespace mask for grid types
        controller:
            index:
                template: "@AppBase/Admin/Abstract/index.html.twig"           
            edit:
                template: "@AppBase/Admin/Abstract/edit.html.twig"