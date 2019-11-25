=== Azure Cognitive Service Personalizer ===
Contributors: eisbermsft
Tags: personalization, recommendation, AI, ML, RL, Azure, Microsoft
Tested up to: 5.3
Requires at least: 3.0
Requires PHP: 5.3
Stable tag: trunk
License: MIT
License URI: https://github.com/microsoft/Personalizer-WordPress/blob/master/LICENSE

Using reinforcement learning provided by Microsoft Azure Cognitive Service Personalizer a machine learning model is used
to recommend posts.

== Description == 

This plugin provides a widget recommending a single post based on behavior observed on this WordPress installation.
Using reinforcement learning provided by [Microsoft Azure Cognitive Service Personalizer](https://azure.microsoft.com/en-us/services/cognitive-services/personalizer/) a machine learning model is trained.
The recommendation is based on a device type, geo location and meta information provided along side the posts. 

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/azure-cognitive-service-personalizer` directory, or install the plugin through the WordPress plugins screen directly.
1. Activate the plugin through the 'Plugins' screen in WordPress
1. Use the Appearance->Widgets screen to add the 'Personalized Post' widget. 
1. Create an Azure Cognitive Service personalizer loop using [Azure Portal](https://ms.portal.azure.com/#create/Microsoft.CognitiveServicesPersonalizer).
1. Each 'Personalized Post' widget can be configured to choose posts from specific cateogries and/or tags.

== Frequently Asked Questions ==

= What data is used by the machine learning model? =

The user agent string is analysed to extract operating system family (e.g. Windows, Mac), 
user agent family (e.g. Edge, Chrome, Safari, ...) as well as device family, brand and model.

If the [geoip-detect plugin](https://wordpress.org/plugins/geoip-detect/) is installed 
the users country and state are resolved.

The posts are characterized by the title, excerpt, categories and tags.

= Are users tracked? =

Individual users are NOT tracked through this plugin. A session cookie is only used to correlate post views with the 
recommended post displayed by the widget.


== Changelog == 

= 0.9 = 
Initial version.
