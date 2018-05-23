 Lf Hiker 
=============
Contributors: epointal  
Donate link: http://elisabeth.pointal.org/lf-hiker/en/about/  
Tags: map, GPX, hiker, runner, track, path, trail, leaflet, profile, openstreetmap,
Requires at least: 4.7.3
Tested up to: 4.9  
Stable tag: 1.0  
Version: 1.9.0
License: GPLv2 or later  
License URI: http://www.gnu.org/licenses/gpl-2.0.html  


**Lf Hiker** is a plugin for quickly display your gpx tracks with their profile elevation on an interactive map. 
 

== Description ==

**Lf Hiker** is a plugin for quickly display your gpx tracks with their profile elevation on an interactive map.  
**Lf Hiker** is responsive and mobile friendly.

### Main Utilisation
Simply upload your gpx file with the wordpress media manager, complete its informations and insert  it in your post/page (shortcode).  

`[lfh-gpx src=url_to_file.gpx  color=darkgreen width=6]A trail in Cevennes[/lfh-gpx]`  

Display the post/page : you have an interactive view of your track.
 
#### The trail is displayed on an OSM map by default, and have its own information window with:  
 * title
 * description
 * interactive **profile elevation**  according to path
 * distance of the track
 * maximum elevation
 * minimum elevation
 * elevation loss
 * elevation gain 
 * trail duration
 * download link of gpx file

For more information go to [Lf Hiker site](http://elisabeth.pointal.org/lf-hiker) 

### List of Features
#### **Lf Hiker** allows displaying too:  
 * few gpx files in the same map
 * markers on the map
 * few maps on the same post/page


#### For gpx track you can custom:  
 * title
 * description
 * stroke color
 * stroke width
 * display button download gpx file   
 And only in shortcode:
* the elevation unit
* the distance unit
* the minimum step on elevation axis (in meter)


**Lf Hiker** included an helper for edit map and add markers. 
You can choose
#### for the map:
 * the tiles layer ( among OSM, OSM_FR , stamen watercolor, arcgis world topo, mapquest *with api key*)
 * to display the fullscreen button
 * the map's view  or let **lf Hiker** find automatically the best view
 * to display the reset button 
 * to display the button "list of layers"
 * the size of the map
 * classnames for the map ( including your custom class)
 * zoom on mousewheel
 * start with profile elevation displayed
 
 
#### for the markers:
 * color of icon marker
 * symbol in the icon
 * title
 * popup
 * visibility according to zoom or not
 * independant window with large description

You can do all this with the helpers or directly using shortcodes  
You can choose to unactive the helper.  

**Lf Hiker** allows you to customize the css in admin configuration (colors of information window, buttons and selected path)  



----------------------------------------  
#### IMPORTANT    
If you think you found a bug in **Lf Hiker** or have any problem/question concerning the plugin, do not hesitate to [contact me](http://elisabeth.pointal.org/lf-hiker/en/contact-me). 

------------------------------------------
### Supported languages 
 * Fran&ccedil;ais (fr_FR) 
 * English (en_US) 
 * Deutsch (de_DE) by [Kristof Kling](https://lg-buggingen.de/bugginger-feierabendlauf/strecke/)  
 
 If you need language of **Lf Hiker** which is not included. You can easily translate with poedit from the file :    
    `lf-hiker/languages/lfh-default.po`.   
	
 I will be happy, to add your translation to **Lf Hiker**.  
If you have any questions about the method, do not hesitate to [contact me](http://elisabeth.pointal.org/lf-hiker/en/contact-me).  
 If you find errors in my english translation do not hesitate to [contact me](http://elisabeth.pointal.org/lf-hiker/en/contact-me).

### Minimum requirements  
 *   Wordpress 4.7.x  
 *   PHP 5.6  
 *   MySQL 5.x 

== Installation  == 
From Plugins Page

1. Log in to the administrator panel.  
2. Go to Plugins Tab  
3. Click on Add New button  
4. Type hiker in the search field  
5. When you found lf-hiker click Install button for upload the plugin on your server  
6. Click Activate button for activate Lf Hiker.  
7. You can change the default configuration in tab: `Settings → Lf-hiker`   

If installation failed, do not hesitate to [contact me](http://elisabeth.pointal.org/lf-hiker/en/contact-me). 

From zip archive

1. Unzip `lf-hiker.zip` 
2. Put the folder `lf-hiker` in directory `wp-content/plugins`
3. Log in to the administrator panel.   
4. Go to Plugins Tab: **Lf Hiker** is among the plugins     
5. Click `Activate` button for activate **Lf Hiker**.    
6. If the activation does not succeed, please [contact me](http://elisabeth.pointal.org/lf-hiker/en/contact-me).  
7. You can change the default configuration in tab: `Settings → Lf-hiker` 

== Changelog == 
= 1.8.2 =
 * Fixed: trouble position of gpx dom block and list block
 * Fixed: trouble with map when tabs (map only on top left corner)
 
= 1.8.1 =
 * Fixed: no icon for way point
 
= 1.8.0 =
 * Evolution: Add black in color path
 * Evolution: Enable all hexadecimal color for path
 * Evolution: Little dots at ends for path with width less than 3px
 * Fixed: german translation
 
= 1.7.0 =
 * Fixed: no path displayed with mesmerize theme
 * Evolution: add tiles Stamen Terrain
 * Evolution: add german translation
 * Fixed: conflict with divi theme and option Grab the first post image
 * Fixed: function boolval do not exists (version php <5.5)
 
= 1.6.0 =
 * Ability to choose the default map tiles
 * Display minimum elevation under profile
 * Display maximum elevation under profile
 * Add parameter step_min for gpx profile elevation 
 * Ability to choose the default step_min for elevation step in settings
 * Disable map pan with one finger
 * Fixed: path and marker button too high in little view
 
= 1.5.0 =
 * Ability to start with profile elevation displayed
 * Ability to manage (hide/show) button download gpx
 * Ability to show/hide gpx path when more than 2 paths
 * Ability to globally configure default settings: fullscreen, button download, start with profile displayed, add checkbox to display/hide gpx
 * Fixed: some points without elevation value
 * Fixed: button list always displayed when parameter list is false
 * Fixed: move line on profile not visible on little screen
 
= 1.4.2 =
 * fixed : issue 2 maps when the first floating the second is in stucks
 * fixed : issue no icon on button
 
= 1.4.1 =
 * fixed map at top (return previous version)
 
= 1.4.0 =
 * fixed issue with custom field ACF dit not do shortcode

= 1.3.9 =
 * fixed fullscreen button to false not working
 
= 1.3.8 =
 * fixed display button add marker in event editor
 
= 1.3.7 =
 * fixed error on activation with PHP5.5
 
= 1.3.6 =
 * smooth profile when lot of points
 * profile with height difference minimum 40 meters 
 * fixed:  characters not center in buttons list and fullscreen 
 * modify track name filter
 
= 1.3.5 =
 * too long title for gpx or marker
 * fixed : url gpx with special character trigger file not found error
 * disabled : map first center on Paris
 
= 1.3.4 =
 * fixed : https for tiles
 * fixed : gpx url for https
 
= 1.3.3 =
 * fixed : On small screens, for three buttons, the elements are not centered
 * fixed : Conflict with the bootstrap themes on hidden elements
 * fixed : list button too big
 
= 1.3.2 =
 * default value attribute visibility set to always for marker
 * update modules for gulp
 * add icons and create files only with used icons
 * fixed : color of the buttons is applied only to the first one on the large screens
 * fixed : added unwanted tag p arround shortcode
 * fixed : no event on button marker when only markers elements
 * fixed : helper, button add marker no event (same issue than previous)
 * fixed : title height issue with some theme
 * fixed : only four buttons visible on small sreens
 * fixed : mousewheel disabled on element window 
 
= 1.3.1 =
 * fixed : Issue color and margin general h1, h2 h3 
 
= 1.3 =
 * More responsive
 * Mobile friendly
 * Display track duration
 
= 1.2.1 =
 * issue map center on Paris when no title attribute in shortcode lfh-marker
 * issue no map or map center on Paris conflict with AccessPress Instagram Feed
 
= 1.2 =
 * Add foot for elevation unit
 * issue for popup when click for a marker from list of elements
 
= 1.1 =
 * Add picture for about page in back office
 * Add miles distance unit for profile elevation
 * issue when shortcode [lfh-map ] without attributes
 
= 1.0.4 =
 * issue when no  mapquest key. 
 * loading asynchrone js and default intialisation for the helper
 * readme file
 
= 1.0.3 =
first stable version


== Upgrade Notice ==
For more informations see [lf Hiker](http://elisabeth.pointal.org/lf-hiker/)

== Screenshots ==
1. Lf Hiker display easily gpx file with profile elevation
2. Lf Hiker is cutomisable
3. Lf Hiker allows to display markers with formated description
4. Lf Hiker manage gpx file
5. Lf Hiker has an helper for add marker
6. Lf Hiker has an helper for edit map
7. You can change some paramaters in administration
8. Lf Hiker is responsive and mobile friendly


== Frequently Asked Questions ==
= How add separate map for a second gpx file in the same post =
You can create a second map with adding shortcode `[lfh-map]` before your gpx file shortcode `[lfh-gpx src="..."][/lfh-gpx]`.

Example:
`[lfh-map]  
[lfh-gpx src=http://url_of_gpx_file1.gpx ]description[/lfh-gpx]  
[lfh-map]  
[lfh-gpx src=http://url_of_gpx_file2.gpx ]description[/lfh-gpx]`  
  
= Where can I create a gpx file ? =

You can find a website list for do this in the page [Track drawing websites](http://wiki.openstreetmap.org/wiki/Track_drawing_websites).    

[Openrunner](http://www.openrunner.com/) is the best one for me and, most important : this website automatically add the elevation data needed to create the profile.  
You can find on this site a quantity of already registered tracks.  

You can also try the tools on [www.mygpsfiles.com](http://www.mygpsfiles.com/app/), easier to use.

= I have a blank page in place of the marker editor =

This trouble come from conflict with multiple `x-frame options` directive in your server.   
Look at whether the plugin succeeded in writing this following lines in the `.htaccess` of your wordpress application if you can:  

`# BEGIN Lf-hiker plugin`  
`<IfModule mod_headers.c>`  
`Header set X-Frame-Options SAMEORIGIN` 
`</IfModule>`  
`# END Lf-hiker plugin ` 
	
It resolve the trouble for me.   
You can find more information about this trouble in wordpress support [Multiple 'X-Frame-Options' headers with conflicting values](https://wordpress.org/support/topic/multiple-x-frame-options-headers-with-conflicting-values-sameorigin-deny/)

