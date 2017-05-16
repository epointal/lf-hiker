 Lf Hiker 
=============
Contributors: epointal  
Donate link: http://elisabeth.pointal.org/lf-hiker/en/about/  
Tags: map, gpx, waypoint, track, path, trail, polyline, markers, leaflet, profile elevation,  openstreetmap, osm, osm_fr, arcgis, mapquest, hiker, runner, elevation gain, elevation loss, distance, walker
Requires at least: 4.7.3
Tested up to: 4.7.4  
Stable tag: 1.0  
Version: 1.0.4
License: GPLv2 or later  
License URI: http://www.gnu.org/licenses/gpl-2.0.html  


**Lf Hiker** is a wordpress plugin for quickly display your gpx tracks with their profile elevation on an interactive map. 
 

== Description ==

### Main Utilisation
Simply upload your gpx file with the wordpress media manager, complete its informations and insert  it in your post/page (shortcode).  
Display the post/page : you have an interactive view of your track.  
#### The trail is displayed on an OSM map by default, and have its own information window with:  
 * title
 * description
 * interactive **profile elevation**  according to path
 * distance of the track
 * elevation loss
 * elevation gain 

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
 
 
#### for the markers:
 * color of icon marker
 * symbol in the icon
 * title
 * popup
 * visibility according to zoom or not
 * independant window with large description

You can do all this with the helpers or directly using shortcodes  
You can choose to unactive the helper.  

**Lf Hiker** allows you to customize the css in admin configuration (colors of information window and selected path)  
You can record your mapquest key or choose the cache directory for the plugin here too.


----------------------------------------  
#### IMPORTANT    
If you think you found a bug in **Lf Hiker** or have any problem/question concerning the plugin, do not hesitate to [contact me](http://elisabeth.pointal.org/lf-hiker/en/contact-me). 

------------------------------------------
### Supported languages 
 * Fran&ccedil;ais (fr_FR) 
 * English (en_US)  
 
 If you need language of **Lf Hiker** which is not included. You can easily translate with poedit from the file :    
    `lf-hiker/languages/lfh-default.po`.   
	
 I will be happy, to add your translation to **Lf Hiker**.  
If you have any questions about the method, do not hesitate to [contact me](http://elisabeth.pointal.org/lf-hiker/en/contact-me).  
 If you find errors in my english translation do not hesitate to [contact me](http://elisabeth.pointal.org/lf-hiker/en/contact-me).
 

== Installation  == 


1. Unzip `lf-hiker.zip` 
2. Put the folder `lf-hiker` in directory `wp-content/plugins`
3. Log in to the administrator panel.   
4. Go to Plugins Tab: **Lf Hiker** is among the plugins     
5. Click `Activate` button for activate **Lf Hiker**.    
6. If the activation does not succeed, please [contact me](http://elisabeth.pointal.org/lf-hiker/en/contact-me).

== Changelog == 
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
2. Lf Hiker allows to display markers with large description
3. Lf Hiker is cutomisable
4. Lf Hiker manage gpx file
5. Lf Hiker has an helper for add marker
6. Lf Hiker has an helper for edit map
7. You can change some parmaters in administration


== Frequently Asked Questions ==

= Where can I create a gpx file ? =

You can find a website list for do this in the page [Track drawing websites](http://wiki.openstreetmap.org/wiki/Track_drawing_websites)   
[Openrunner](http://www.openrunner.com/) is the best one for me and, most important : this website automatically add the elevation data needed to create the profile.  
You can find on this site a quantity of already registered tracks.

= I have a blank page in place of the marker editor =

This trouble come from conflict with multiple `x-frame options` directive in your server. Look at whether the plugin succeeded in writing this following lines in the `.htaccess` of your wordpress application if you can:
    `# BEGIN Lf-hiker plugin`  
    `&lt;IfModule mod_headers.c>`  
    `Header set X-Frame-Options SAMEORIGIN` 
    `&lt;/IfModule>`  
    `# END Lf-hiker plugin ` 
	
It resolve the trouble for me.   
You can find more information about this trouble in wordpress support [Multiple 'X-Frame-Options' headers with conflicting values](https://wordpress.org/support/topic/multiple-x-frame-options-headers-with-conflicting-values-sameorigin-deny/)