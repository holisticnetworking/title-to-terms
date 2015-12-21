# Title to Terms Ultimate
The ultimate auto-tagging plugin for WordPress.

Title to Terms Ultimate is for every WordPress admin that needs tags, categories and other taxonomies filled out, but rarely gets what they need. For each post type in your WordPress installation, T2TU will allow you to assign a taxonomy to be automatically updated with keywords pulled from the title of the post.

Every time a post gets saved or updated, T2TU analyzes the title of the post for usable keywords. A list of user-configurable "Stop Words" is checked by the parser, so that words like "I" or "going" don't get made into ultimately useless tags.

T2TU can be configured to either add new tags to a post or append additional tags.

## Download and Installation
* Download the plugin from the WordPress repository, [here](https://wordpress.org/plugins/title-to-tags/ "Titles to Tags").
* Unzip and upload the file to your /wp-content/plugins directory.
* In the Dashboard, go to Plugins->All Plugins and activate.
* Configure T2TU from the Settings->Writing screen

## Configuration Options
* __Title to Terms: ignored words__ ~ This is a list of Stop Words that will be ignored by the title parser. You can add new terms to and delete terms from this list. You can also set the list back to default values by simply deleting the entire list.
* __Title to Terms: append tags__ ~ By default, T2TU leaves posts alone if they've already got terms in the chosen taxonomy. By checking this box, you can append terms to an existing list.
* __Title to Terms: Taxonomies and Post Types__ ~ This is a matrix of post types and their associated terms. You can select one taxonomy from each post type to autofill.
