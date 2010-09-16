# Drupal Doxygen Generation

This package provides what is needed to generate project Doxygen documentation for a site or module.

To create Drupal 6 documentation, as opposed to that for a site or modules, see the [Drupal 6 Doxygen](http://github.com/mattfarina/drupal-6-doxygen) project.

## Dependencies

[Doxygen Website](http://www.stack.nl/~dimitri/doxygen)

## Generating Site Documentation

This method is for generating site documentation for a full site.

 * In the config.doxy file the PROJECT_NAME to the name of the project and PROJECT_NUMBER to the
   version of the project.
 * And the drupal site to the base directory of this project.
 * In the file documentation/docs.php update the page title and description to that of the project.
   Add additional pages as needed following the [Doxygen Commands](http://www.stack.nl/~dimitri/doxygen/commands.html) for the page tag.
 * From the command link execute `doxygen config.doxy`. This will generate the documentation and put it in the new directory docs/html.
 * Open up your browser to docs/html/index.html.

## Generating Module Documentation

This method is for generating module documentation. 

 * In the config.doxy file the PROJECT_NAME to the name of the module and PROJECT_NUMBER to the
   version of the module.
 * And the module to the base directory of this project.
 * Remove the developer directory. This file holds the hook documentation for Drupal 6 and is not
   needed for a module.
 * In the file documentation/docs.php update the page title and description to that of the module.
   Add additional pages as needed following the [Doxygen Commands](http://www.stack.nl/~dimitri/doxygen/commands.html) for the page tag.
 * From the command link execute `doxygen config.doxy`. This will generate the documentation and put it in the new directory docs/html.
 * Open up your browser to docs/html/index.html.

## Custom Configs

Doxygen has a lot of configuration options. The ones chosen here are to my preference but you may want something different.

For more details on how to configure Doxygen you can read the commends in the config file or the [Online Documentation](http://www.stack.nl/~dimitri/doxygen/config.html)
