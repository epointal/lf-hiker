# Lf Hiker 
[![Contributor: Elisabeth Pointal](http://elisabeth.pointal.org/images/svg/contributor2.svg)](http://elisabeth.pointal.org)
[![License GPL 2](http://elisabeth.pointal.org/images/svg/license.svg)](http://www.gnu.org/licenses/gpl-2.0.html )
[![Version 1.2.1](http://elisabeth.pointal.org/images/svg/version1.2.1.svg)](https://github.com/epointal/lf-hiker/archive/1.2.1.zip) 

Plugin site web: [Lf Hiker](http://elisabeth.pointal.org/lf-hiker/en/about/ )  
Requires Wordpress version: 4.7.3  
Tested up to: Wordpress 4.8  
Stable version: 1.0    




**Lf Hiker** is a wordpress plugin for quickly display your gpx tracks with their profile elevation on an interactive map. 
 

Description
-------------
### Main Utilisation
Simply upload your gpx file with the wordpress media manager, complete its informations and insert  it in your post/page (shortcode).  
Display the post/page : you have an interactive view of your track.  
The trail is displayed on an OSM map by default, and have its own information window with: 
* title
* description
* interactive **profile elevation**  according to path
* distance of the track
* elevation loss
* elevation gain 

![Lf Hiker Front](http://elisabeth.pointal.org/lf-hiker/wp-content/uploads/2017/04/gpx-stevenson-3.png) 

### List of Features
**Lf Hiker** allows displaying too:
* few gpx files in the same map
* markers on the map
* few maps on the same post/page


For gpx track you can custom:
* title
* description
* stroke color
* stroke width


**Lf Hiker** included an helper for edit map and add markers. 
You can choose
#### for the map:
 * the tiles layer ( among OSM, OSM_FR , stamen watercolor, arcgis world topo, mapquest *with api key*)
 * fullscreen button or not
 * map's view  or let **lf Hiker** find automatically the best view
 * reset button  or not
 * list of layers button or not
 * size of the map
 * classname for the map ( including your custom class)
 * zoom on mousewheel or not
 
 
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
## Supported languages 
 * Fran&ccedil;ais (fr_FR) 
 * English (en_US)  
 
 If you need language for **Lf Hiker** which is not included. You can easily translate with poedit from the file :    
    `lf-hiker/languages/lfh-default.po`.   
	
 I will be happy to add your translation to **Lf Hiker**.  
If you have any questions about the method, do not hesitate to [contact me](http://elisabeth.pointal.org/lf-hiker/en/contact-me).  
 If you find errors in my english translation do not hesitate to [contact me](http://elisabeth.pointal.org/lf-hiker/en/contact-me).
 

## Installation   

  

### Minimum requirements.  
*   Wordpress 4.7  
*   PHP 5.x  
*   MySQL 5.x  


### Perform a new installation  

After downloading the ZIP file [lf-hiker-1.2.zip](https://github.com/epointal/lf-hiker/archive/1.2.zip)   

1. Unzip `lf-hiker.zip` 
2. Put the folder `lf-hiker` in directory `wp-content/plugins`
3. Log in to the administrator panel.   
4. Go to Plugins Tab: **Lf Hiker** is among the plugins     
5. Click `Activate` button for activate **Lf Hiker**.    
6. If the activation does not succeed, please [contact me](http://elisabeth.pointal.org/lf-hiker/en/contact-me).


### Browsers
 Tested with
* Firefox 52.0
* Chrome 57.0
* Microsoft Edge
* Internet Explorer 11
* Safari 5 (windows)
* Opera 44

### Use
* [leaflet](http://leafletjs.com) an open-source JavaScript library for mobile-friendly interactive maps
* [leaflet-gpx](https://github.com/mpetazzoni/leaflet-gpx) a leaflet plugin for the analysis and parsing of a GPX track 
* [font-awesome-4.7.0](http://fontawesome.io/) for iconic font
* [awesome-marker](https://github.com/lvoogdt) Colorful, iconic  markers by Lennard Voogdt


## Screenshots 
### Front screenshots
![Lf Hiker Front gpx](http://elisabeth.pointal.org/lf-hiker/wp-content/uploads/2017/04/gpx-stevenson-3.png) 
![Lf Hiker Front Marker](http://elisabeth.pointal.org/lf-hiker/wp-content/uploads/2017/04/notre_dame.png) 
![Lf Hiker Front customise](http://elisabeth.pointal.org/lf-hiker/wp-content/uploads/2017/04/notre_dame_cutom.png)

### Post editing screenshots
![Lf Hiker Manage gpx files](http://elisabeth.pointal.org/lf-hiker/wp-content/uploads/2017/04/manage-gpx-3.png)  
![Lf Hiker Helper editor for markers](http://elisabeth.pointal.org/lf-hiker/wp-content/uploads/2017/04/add-marker-3.png)  
![Lf Hiker Helper editor for map](http://elisabeth.pointal.org/lf-hiker/wp-content/uploads/2017/04/edit-map-2.png)  

### Configuration screenshot
![Lf Hikder Admin](http://elisabeth.pointal.org/lf-hiker/wp-content/uploads/2017/04/config-lfh-3.png)

## Changelog 
### 1.2.1
 * issue when no title attribute in shortcode lfh-marker
 
### 1.2
 * Add foot for elevation unit
 * issue for popup when click for a marker from list of elements

### 1.1 
 * Add picture for about page in back office
 * Add milles distance unit for profile elevation
 * issue when shortcode [lfh-map ] without attributes
 
### 1.0.4 
 * issue when no  mapquest key. 
 * loading asynchrone js and default intialisation for the helper
 * readme file
 
### 1.0.3 
first stable version


## Frequently Asked Questions 

**1. How add separate map for a second gpx file in the same post **

----------------------
You can create a second map with adding shortcode `[lfh-map]` before your gpx file shortcode `[lfh-gpx src="..."][/lfh-gpx]`.

Example:
  `[lfh-map]`
  `[lfh-gpx src=http://url_of_gpx_file1.gpx ]description[/lfh-gpx]`
  `[lfh-map]`
  `[lfh-gpx src=http://url_of_gpx_file2.gpx ]description[/lfh-gpx]`

**2. Where can I create a gpx file ?**

--------------------
You can find a website list for do this in the page [Track drawing websites](http://wiki.openstreetmap.org/wiki/Track_drawing_websites)   
[Openrunner](http://www.openrunner.com/) is the best one for me and the most important: this website automatically add the elevation data needed to create the profile.  
You can find on this site a quantity of already registered tracks.

-------------------------
**3. I have a blank page in place of the marker editor**

------------------------------------------
This trouble come from conflict with multiple `x-frame options` directive in your server. Look at whether the plugin succeeded in writing this following lines in the `.htaccess` of your wordpress application if you can:
```	
    # BEGIN Lf-hiker plugin
    <IfModule mod_headers.c>
    Header set X-Frame-Options SAMEORIGIN
    </IfModule>
    # END Lf-hiker plugin 
```
It resolve the trouble for me.   
You can find more information about this trouble in wordpress support [Multiple 'X-Frame-Options' headers with conflicting values](https://wordpress.org/support/topic/multiple-x-frame-options-headers-with-conflicting-values-sameorigin-deny/)