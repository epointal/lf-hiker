String.prototype.addslashes = function () { return this.replace(/[\\"']/g, '\\$&').replace(/\u0000/g, '\\0'); };
String.prototype.stripslashes = function () { return this.replace(/\\(.?)/g, function (s, n1) { switch (n1) {
    case '\\': return '\\';
    case '0': return '\u0000';
    case '': return '';
    default: return n1;
} }); };
String.prototype.replaceAll = function (search, replacement) {
    var target = this;
    return target.replace(new RegExp(search, 'g'), replacement);
};
(function () {
    //For Adding button mode  on map control
    //var LfhMarkerOptions = L.MarkerOption
    var LfhControl = L.Control.extend({
        options: {
            position: 'topleft'
        },
        onAdd: function (map) {
            var container = document.querySelector('#lfh-control');
            return container;
        }
    });
    var center = [48.67777858405578, 2.166026472914382];
    var lfh = {
        mode: "lfh-view",
        confirm: data_helper.confirm,
        add_description: data_helper.add_description,
        tiles: data_helper.tiles,
        map: null,
        center: center,
        zoom: 2,
        map_var: new Array(),
        current_marker: null,
        default_icon: null,
        move_marker: null,
        markers: new Array(),
        tile: null,
        record: null,
        init_map: function () {
            lfh.map = L.map('map').setView(lfh.center, lfh.zoom);
            lfh.set_tile();
            lfh.map.addControl(new LfhControl());
            //handler init event when control is added
            var nodes = document.querySelectorAll('.marker-control');
            [].forEach.call(nodes, function (button) {
                L.DomEvent.addListener(button, 'click', function (e) {
                    lfh.set_mode(button);
                    e.stopPropagation();
                });
            });
            lfh.default_icon = L.AwesomeMarkers.icon({
                icon: 'circle',
                prefix: 'lfhicon',
                markerColor: 'red'
            });
            lfh.move_marker = L.marker(lfh.center).addTo(lfh.map);
            lfh.move_marker.setOpacity(0);
            //handler event
            lfh.map.on("mousemove", function (e) {
                switch (lfh.mode) {
                    case 'lfh-add-marker':
                        lfh.move_marker.setLatLng(e.latlng);
                        break;
                    default:
                }
            });
            lfh.map.on('click', function (e) {
                if (lfh.mode == 'lfh-add-marker') {
                    lfh.add_marker(e);
                }
            });
        },
        set_tile: function () {
            var tilename = document.querySelector('select[name="lfh-form-map-tile"]').value;
            var tileinfo = lfh.tiles[tilename];
            if (lfh.tile != null) {
                lfh.map.removeLayer(lfh.tile);
            }
            if (tilename != 'mapquest') {
                lfh.tile = L.tileLayer(tileinfo.url, {
                    attribution: tileinfo.attribution
                });
            }
            else {
                lfh.tile = MQ.mapLayer();
            }
            lfh.tile.addTo(lfh.map);
            // lfh.map.options.minZoom = tileinfo.min_zoom;
            // lfh.map.options.minZoom = tileinfo.max_zoom;
        },
        add_marker: function (e) {
            lfh.current_marker = L.marker(e.latlng, {
                icon: lfh.default_icon,
                draggable: true,
                index: lfh.markers.length,
                title: '',
                popup: '',
                description: false,
                visibility: 'always'
            }).addTo(lfh.map);
            lfh.markers.push(lfh.current_marker);
            lfh.current_marker.on('click', function (e) {
                switch (lfh.mode) {
                    case 'lfh-edit-marker':
                        lfh.current_marker = this;
                        lfh.init_window();
                        break;
                    case 'lfh-delete-marker':
                        lfh.current_marker = null;
                        lfh.delete_marker(this);
                        break;
                    default:
                }
            });
            //switch to edit mode
            lfh.set_mode(document.querySelector('#lfh-edit-marker'));
            return lfh.current_marker;
        },
        delete_marker: function (marker) {
            var answer = confirm(lfh.confirm);
            if (!answer) {
                return;
            }
            var index = marker.options.index;
            for (var i = index; i < lfh.markers.length - 1; i++) {
                lfh.markers[i] = lfh.markers[i + 1];
                lfh.markers[i].options.index = i;
            }
            lfh.map.removeLayer(marker);
            lfh.markers.pop();
        },
        //change mode from node button
        set_mode: function (node) {
            //unactive the active
            if (lfh.mode != 'lfh-view') {
                var active = document.querySelector('.marker-control.active');
                active.className = active.className.replace(' active', '');
            }
            //close window edit
            var nodes = document.querySelectorAll('.lfh-form-edit');
            [].forEach.call(nodes, function (node) {
                node.style.display = 'none';
            });
            if (node.id == lfh.mode) {
                lfh.mode = 'lfh-view';
            }
            else {
                //active the mode
                lfh.mode = node.id;
                node.className = node.className + " active";
                //case  add see marker window
                lfh.init_window();
            }
            if (lfh.mode == 'lfh-add-marker') {
                //show move_marker
                lfh.move_marker.setOpacity(1);
            }
            else {
                //hide move_marker
                lfh.move_marker.setOpacity(0);
            }
        },
        //get position center and zoom of the map, record and write it
        get_position_info: function () {
            lfh.record = {
                center: lfh.map.getCenter(),
                zoom: lfh.map.getZoom()
            };
            lfh.write_position();
        },
        write_position: function () {
            var content = '<b> lat</b> = ' + lfh.record.center.lat + '<br />';
            content += '<b> lng</b> = ' + lfh.record.center.lng + '<br />';
            content += '<b> zoom</b> = ' + lfh.record.zoom;
            document.querySelector('#map-position').innerHTML = content;
        },
        //open window with good information
        init_window: function () {
            switch (lfh.mode) {
                case 'lfh-edit-map':
                    document.querySelector('#window-edit-map').style.display = 'block';
                    break;
                case 'lfh-edit-marker':
                    if (lfh.current_marker == null)
                        return;
                    //init the window "edit marker" with information of current_marker
                    //from current marker to edit window
                    var options = lfh.current_marker.options;
                    var color = options.icon.options.markerColor;
                    var icon = options.icon.options.icon;
                    document.querySelector('#window-edit-marker input[name="title"]').value = options.title.stripslashes();
                    document.querySelector('#window-edit-marker textarea[name="popup"]').value = options.popup.stripslashes();
                    document.querySelector('#window-edit-marker input[name="description"]').checked = options.description;
                    document.querySelector('#window-edit-marker select[name="visibility"]').selectedIndex = (options.visibility == 'always') ? 0 : 1;
                    var evt = document.createEvent('MouseEvents');
                    evt.initEvent("click", true, true);
                    document.querySelector('#color-marker div.awesome-marker-icon-' + color).dispatchEvent(evt);
                    document.querySelector('#icon-marker div.lfhicon-' + icon).dispatchEvent(evt);
                    document.querySelector('#window-edit-marker').style.display = 'block';
                    break;
            }
        },
        // close the widow "edit marker"
        close_window: function (id) {
            switch (id) {
                case 'window-edit-marker':
                    lfh.close_color();
                    lfh.close_icon();
                    document.querySelector('#' + id).style.display = 'none';
                    break;
                case 'window-edit-map':
                    var evt = document.createEvent('MouseEvents');
                    evt.initEvent("click", true, true);
                    document.querySelector('#lfh-edit-map').dispatchEvent(evt);
                    break;
            }
        },
        close_color: function () {
            var label = document.querySelector('#window-edit-marker .to-extend label ');
            var div = label.parentNode.querySelector("div");
            if (label.textContent == '-') {
                label.textContent = '+';
                div.style.display = 'none';
            }
        },
        close_icon: function () {
            var label = document.querySelector('#window-edit-marker .to-extend:last-child label');
            var div = label.parentNode.querySelector("div");
            if (label.textContent == '-') {
                label.textContent = '+';
                div.style.display = 'none';
            }
        },
        write_checked: function (name) {
            var str = ' ' + name + '=' + document.querySelector('input[name="' + lfh.map_var[name] + '"]').checked;
        },
        shortcode_map: function () {
            var shortcode = '';
            shortcode += '[lfh-map ';
            if (!document.querySelector('input[name="lfh-form-map-autocenter"]').checked) {
                shortcode += ' lat=' + lfh.record.center.lat;
                shortcode += ' lng=' + lfh.record.center.lng;
                shortcode += ' zoom=' + lfh.record.zoom;
            }
            if (document.querySelector('input[name="lfh-form-map-class"]').value != '') {
                shortcode += ' class="' + document.querySelector('input[name="lfh-form-map-class"]').value.replaceAll('-', '\\-') + '"';
            }
            ['autocenter', 'fullscreen', 'reset', 'list', 'mousewheel', 'open'].forEach(function (key) {
                shortcode += ' ' + key + '=' + document.querySelector('input[name="lfh-form-map-' + key + '"]').checked;
            });
            ['width', 'height'].forEach(function (key) {
                shortcode += ' ' + key + '=' + document.querySelector('input[name="lfh-form-map-' + key + '"]').value;
            });
            shortcode += ' tile=' + document.querySelector('select[name="lfh-form-map-tile"]').value;
            shortcode += ' ] <br /> ';
            return shortcode;
        },
        shortcode: function (name) {
            var shortcode = '';
            switch (name) {
                case 'map':
                    shortcode += lfh.shortcode_map();
                    break;
                case 'markers':
                    for (var i = 0; i < lfh.markers.length; i++) {
                        var latlng = lfh.markers[i].getLatLng();
                        var options = lfh.markers[i].options;
                        var color = options.icon.options.markerColor;
                        var icon = options.icon.options.icon;
                        shortcode += '[lfh-marker lat=' + latlng.lat + ' lng=' + latlng.lng;
                        shortcode += ' color=' + color + ' icon=' + icon;
                        shortcode += ' title="' + options.title + '"';
                        shortcode += ' popup="' + options.popup + '"';
                        shortcode += ' visibility=' + options.visibility;
                        shortcode += ' ]';
                        if (options.description) {
                            shortcode += '<br />' + lfh.add_description + '<br />';
                        }
                        shortcode += '[/lfh-marker]<br />  ';
                    }
            }
            return shortcode;
        }
    };
    //handler add when buttons control are placed on map
    /*var nodes = document.querySelectorAll('.marker-control');
    [].forEach.call(nodes, function(button){
        L.DomEvent.addListener(button,'click', function(e){
            lfh.set_mode(button);
            e.stopPropagation();
        });
    });*/
    //no submit form
    L.DomEvent.addListener(document.querySelector('form'), 'submit', function (e) {
        e.preventDefault();
    });
    // draggable edit marker window
    var hdrg = {
        selected: null,
        x_pos: 0,
        y_pos: 0,
        x_elem: 0,
        y_elem: 0,
        // Will be called when user starts dragging an element
        init: function (elem) {
            // Store the object of the element which needs to be moved
            hdrg.selected = elem;
            hdrg.x_elem = hdrg.x_pos - hdrg.selected.offsetLeft;
            hdrg.y_elem = hdrg.y_pos - hdrg.selected.offsetTop;
        },
        // Will be called when user dragging an element
        move: function (e) {
            hdrg.x_pos = document.all ? window.event.clientX : e.pageX;
            hdrg.y_pos = document.all ? window.event.clientY : e.pageY;
            if (hdrg.selected !== null) {
                var left = Math.min(Math.max(-200, hdrg.x_pos - hdrg.x_elem), window.innerWidth - 100);
                hdrg.selected.style.left = left + 'px';
                var top = Math.min(Math.max(0, hdrg.y_pos - hdrg.y_elem), window.innerHeight - 40);
                hdrg.selected.style.top = top + 'px';
            }
        },
        // Destroy the object when we are done
        destroy: function () {
            hdrg.selected = null;
        }
    };
    // Bind the functions...
    document.onmousemove = hdrg.move;
    document.onmouseup = hdrg.destroy;
    //add drag listener
    var headers = document.querySelectorAll('.lfh-form-edit .header');
    [].forEach.call(headers, function (header) {
        L.DomEvent.addListener(header, 'mousedown', function (e) {
            hdrg.init(header.parentNode);
        });
    });
    //- end drag
    // event on elements of window edit marker and edit map
    var nodes = document.querySelectorAll('.lfhicon-close');
    [].forEach.call(nodes, function (node) {
        L.DomEvent.addListener(node, 'click', function (e) {
            var id = node.parentNode.parentNode.id;
            lfh.close_window(id);
        });
    });
    // show/hide extend part of form
    var nodes = document.querySelectorAll(".to-extend label");
    [].forEach.call(nodes, function (label) {
        L.DomEvent.addListener(label, 'click', function (e) {
            var label = e.target;
            var div = label.parentNode.querySelector("div");
            if (label.textContent == "+") {
                label.textContent = "-";
                div.style.display = "inline-block";
            }
            else {
                label.textContent = "+";
                div.style.display = "none";
            }
        });
    });
    // - for edit map
    //-----------------
    var node = document.querySelector('#window-edit-map input[name="lfh-form-map-autocenter"]');
    L.DomEvent.addListener(node, 'click', function (e) {
        document.querySelector('#center-map').style.display = e.target.checked ? 'none' : 'block';
        lfh.get_position_info();
    });
    var node = document.querySelector('input[name="save-center"]');
    L.DomEvent.addListener(node, 'click', function (e) {
        lfh.get_position_info();
    });
    var node = document.querySelector('#window-edit-map select[name="lfh-form-map-tile"]');
    L.DomEvent.addListener(node, 'change', function (e) {
        lfh.set_tile();
    });
    var evt = document.createEvent('MouseEvents');
    evt.initEvent("click", true, true);
    document.querySelector('#window-edit-map input[type="reset"]').dispatchEvent(evt);
    document.querySelector('#window-edit-map form').onreset = function () {
        document.querySelector('#center-map').style.display = 'none';
        setTimeout(function () {
            if (lfh.tiles != null)
                lfh.set_tile();
        }, 500);
    };
    // - for edit marker
    var node = document.querySelector('#window-edit-marker input[name="title"]');
    L.DomEvent.addListener(node, 'change', function (e) {
        lfh.current_marker.options.title = this.value.addslashes();
    });
    var node = document.querySelector('#window-edit-marker textarea[name="popup"]');
    L.DomEvent.addListener(node, 'change', function (e) {
        var content = this.value;
        lfh.current_marker.options.popup = this.value.addslashes();
        lfh.current_marker.bindPopup(content).openPopup();
    });
    var node = document.querySelector("#window-edit-marker input[name='description']");
    L.DomEvent.addListener(node, 'click', function (e) {
        lfh.current_marker.options.description = this.checked;
    });
    var node = document.querySelector("#window-edit-marker select");
    L.DomEvent.addListener(node, 'change', function (e) {
        lfh.current_marker.options.visibility = this.value;
    });
    var nodes = document.querySelectorAll("#selected-icon , #selected-color");
    [].forEach.call(nodes, function (div) {
        L.DomEvent.addListener(div, 'focus', function (e) {
            var node = div.parentNode.nextSibling;
            while (node.nodeType != 1) {
                node = node.nextSibling;
            }
            node.querySelector("label").textContent = "-";
            node.querySelector("div").style.display = "inline-block";
            if (div.id == 'selected-icon') {
                lfh.close_color();
            }
        });
    });
    var nodes = document.querySelectorAll('#color-marker div.awesome-marker');
    [].forEach.call(nodes, function (div) {
        L.DomEvent.addListener(div, 'click', function (e) {
            var div = this;
            var node = document.querySelector('#color-marker div.selected');
            var classname = node.className.replace(' selected', '');
            node.className = classname;
            classname = div.className;
            div.className = classname + " selected";
            var color = div.dataset.value;
            var node = document.querySelector('#selected-color');
            node.className = classname;
            node.dataset.value = color;
            //change the marker
            var icon = document.querySelector('#selected-icon').dataset.value;
            lfh.current_marker.setIcon(L.AwesomeMarkers.icon({
                icon: icon,
                prefix: 'lfhicon',
                markerColor: color
            }));
        });
    });
    var nodes = document.querySelectorAll('#icon-marker div.lfhicon');
    [].forEach.call(nodes, function (div) {
        L.DomEvent.addListener(div, 'click', function (e) {
            var div = this;
            var node = document.querySelector('#icon-marker div.selected');
            var classname = node.className.replace(' selected', '');
            node.className = classname;
            classname = div.className;
            div.className = classname + " selected";
            var icon = div.dataset.value;
            var node = document.querySelector('#selected-icon');
            node.className = classname;
            node.dataset.value = icon;
            //change the marker
            var color = document.querySelector('#selected-color').dataset.value;
            lfh.current_marker.setIcon(L.AwesomeMarkers.icon({
                icon: icon,
                prefix: 'lfhicon',
                markerColor: color
            }));
        });
    });
    //buttons insert and cancel
    var node = document.querySelector('#banner input[name="lfh-cancel"]');
    L.DomEvent.addListener(node, 'click', function (e) {
        // only close the window
        if (typeof window.parent != 'undefined' && typeof window.parent.tinymce != 'undefined') {
            window.parent.tinymce.activeEditor.windowManager.close();
        }
    });
    var node = document.querySelector('#banner input[name="lfh-insert"]');
    L.DomEvent.addListener(node, 'click', function (e) {
        //open modal
        document.querySelector("#fade").className = '';
    });
    var node = document.querySelector('#fade input[name="lfh-modal-cancel"]');
    L.DomEvent.addListener(node, 'click', function (e) {
        //open modal
        document.querySelector("#fade").className = 'hidden';
        //document.querySelector('#debug').innerHTML = lfh.shortcode('map');
        // only close the window
    });
    var node = document.querySelector('#fade input[name="lfh-modal-insert"]');
    L.DomEvent.addListener(node, 'click', function (e) {
        var shortcode = '';
        if (document.querySelector('#fade input[name="lfh-insert-map"]').checked) {
            shortcode += lfh.shortcode('map');
        }
        if (document.querySelector('#fade input[name="lfh-insert-markers"]').checked) {
            shortcode += lfh.shortcode('markers');
        }
        // only close the window
        if (typeof window.parent != 'undefined' && typeof window.parent.tinymce != 'undefined') {
            window.parent.tinymce.activeEditor.selection.setContent(shortcode);
            window.parent.tinymce.activeEditor.windowManager.close();
        }
    });
    window.onload = function () {
        if (window.dialogArguments) {
            var arguments = window.dialogArguments;
        }
        else {
            var arguments = window.opener;
        }
        if (arguments != null && typeof arguments['lat'] != 'undefined') {
            lfh.center = [arguments['lat'], arguments['lng']];
            lfh.zoom = arguments['zoom'];
        }
        if (lfh.map == null) {
            lfh.init_map();
        }
    };
    if (lfh.map == null) {
        lfh.init_map();
    }
})();
