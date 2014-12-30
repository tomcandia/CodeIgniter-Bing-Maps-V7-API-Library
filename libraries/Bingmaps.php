<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Bingmaps {

    var $credentials                    = "";
    var $map_height                     = '450px';
    var $map_width                      = '100%';
    var $map_name                       = 'map';
    var $map_div_id                     = "map-canvas";
    var $https                          = TRUE;
    var $language                       = '';
    
    // Map Options
    var $backgroundColor                = '';
    var $customizeOverlays              = FALSE;
    var $disableBirdseye                = FALSE;
    var $disableKeyboardInput           = FALSE;
    var $disableMouseInput              = FALSE;
    var $disablePanning                 = FALSE;
    var $disableTouchInput              = FALSE;
    var $disableUserInput               = FALSE;
    var $disableZooming                 = FALSE;
    var $enableClickableLogo            = FALSE;
    var $enableSearchLogo               = FALSE;
    var $fixedMapPosition               = FALSE;
    var $inertiaIntensity               = '';
    var $showBreadcrumb                 = FALSE;
    var $showCopyright                  = FALSE;
    var $showDashboard                  = TRUE;
    var $showMapTypeSelector            = TRUE;
    var $showScalebar                   = TRUE;
    var $theme                          = '';
    var tileBuffer                      = '';
    var useInertia                      = TRUE;
    
    // View Options
    var $animate                        = TRUE;
    var $bounds                         = '';
    var $center				            = "-33.468108, -70.64209";
    var $centerOffset                   = '';
    var $heading                        = '';
    var $labelOverlay                   = '';
    var $map_type                       = 'aerial';
    var $padding                        = '';
    var $zoom                           = 13;
    
    var $onclick_bing                   = '';
    var $ondragend_bing                 = '';
    
    var $markers                        = array();
    var $markersInfo                    = array();
    
    var $pins                           = array();
    var $pinsInfo                       = array();
    
    function Bingmaps($config = array()) {
        
        if(count($config)>0) {
            $this->init($config)
        }
        
    }
    
    function init($config = array()) {
        foreach ($config as $key => $val) {
            if (isset($this->$key)) {
                $this->$key = $val;
            }
        }
    }
    
    function add_marker($params = array()){
        
        $marker = array();
        $this->markersInfo['marker_'.count($this->markers)] = array();
        
        $marker['position']         = '';
        $marker['icon']             = '';
        $marker['draggable']        = 'false';
        $marker['ondragend_bing']   = '';
        
        $marker_output = '';
        
        foreach ($params as $key => $value) {
		
            if (isset($marker[$key])) {

                $marker[$key] = $value;

            }

        }
        
        $marker_id = count($this->markers);
        if (trim($marker['id']) != "")
        {
            $marker_id = $marker['id'];
        }
        
        if($marker['position']!= ''){
            if($this->is_lat_long($marker['position'])){
                $marker_output .= 'var loc_'.$marker_id.' = new Microsoft.Maps.Location('.$marker['position'].');'; 
                $explodePosition = explode(",", $marker['position']);
                $this->markersInfo['marker_'.$marker_id]['latitude'] = trim($explodePosition[0]);
                $this->markersInfo['marker_'.$marker_id]['longitude'] = trim($explodePosition[1]);
            }
        
        
            $marker_output .= '
                var markerOptions = {
                    draggable: '.$marker['draggable'];
            if($marker['icon'] != '') {
                $markerOptions .= ',
                    icon: '.$marker['icon'];
            }
            $marker_output .= ' }
                var pin_'.$marker_id.' = new Microsoft.Maps.Pushpin(loc_'.$marker_id.',markerOptions);
                dataLayer.push(pin_'.$marker_id.');';

            if ($marker['ondragend_bing']!="") { 
                    $marker_output .= '
                    Microsoft.Maps.Events.addHandler(pin_'.$marker_id.', "dragend", function(event) {
                            '.$marker['ondragend_bing'].'
                    });
                    ';
            }
            array_push($this->markers,$marker_output);
        }
        
    }
    
    function add_pin($params) {
        
        $pin = array();
        $pinInfo = array();
        
        // Pin Options
        $pin['id']              = '';
        $pin['position']        = '';
        $pin['anchor']          = '';
        $pin['draggable']       = FALSE;
        $pin['height']          = '39';
        $pin['htmlContent']     = '';
        $pin['icon']            = '';
        $pin['infobox']         = '';
        $pin['state']           = '';
        $pin['text']            = '';
        $pin['textOffset']      = '';
        $pin['typeName']        = '';
        $pin['visible']         = TRUE;
        $pin['width']           = '25';
        $pin['zIndex']          = '';
        
        // Pin Events
        $pin['click']           = '';
        $pin['dbclick']         = '';
        $pin['drag']            = '';
        $pin['dragend']         = '';
        $pin['dragstart']       = '';
        $pin['entitychanged']   = '';
        $pin['mousedown']       = '';
        $pin['mouseout']        = '';
        $pin['mouseover']       = '';
        $pin['mouseup']         = '';
        $pin['rightclick']      = '';
        
        $output = '';
        
        foreach($params as $key => $value) {
            
            if(isset($pin[$key])) {
                $pin[$key] = $value;
            }
            
        }
        
        $pin_id = count($this->pins);
        if(trim($pin['id']) != '') {
            $pin_id = $pin['id'];
        }
        
        if($pin['position'] != '') {
            if(is_lat_long($pin['position'])) {
                $output .= '
            var loc = new Microsoft.Maps.Location('.$pin['position'].');
                ';
                $explodePosition = explode(',', $pin['position']);
                $pinInfo['latitude'] = trim($explodePosition[0]);
                $pinInfo['longitude'] = trim($explodePosition[1]);
            }
            
            // TODO: get lat long from address
        }
        
        $output .= '
            var pinOptions = {
                height: '.$pin['height'].',
                width: '.$pin['width'];
        
        if($pin['anchor'] != '') {
            $output .= ',
                anchor: new Microsoft.Maps.Point('.$pin['anchor'].')';
        }
        
        if($pin['draggable']) {
            $output .= ',
                draggable: true';
        }
        
        if($pin['htmlContent'] != '') {
            $output .= ',
                htmlContent: \''.$pin['htmlContent'].'\'';
        }
        
        if($pin['icon'] != '') {
            $output .= ',
                icon: \''.$pin['icon'].'\'';
        }
        
        if($pin['infobox'] != '') {
            $output .= ',
                infobox: \''.$pin['infobox'].'\'';
        }
        
        if($pin['state'] != '') {
            $output .= ',
                state: Microsoft.Maps.EntityState.'.$pin['state'];
        }
        
        if($pin['text'] != '') {
            $output .= ',
                text: \''.$pin['text'].'\'';
        }
        
        if($pin['textOffset'] != '') {
            $output .= ',
                textOffset: new Microsoft.Maps.Point('.$pin['textOffset'].')';
        }
        
        if($pin['typeName'] != '') {
            $output .= ',
                typeName: \''.$pin['typeName'];
        }
        
        if(!$pin['visible'] != '') {
            $output .= ',
                visible: false';
        }
        
        if($pin['zIndex'] != '') {
            $output .= ',
                zIndex: '.$pin['zIndex'];
        }
        
        $output .= '
            };
            var pin_'.$pin_id.' = new Microsoft.Maps.Pushpin(loc,pinOptions);
            ';
        
        if($pin['click'] != '') {
            $output .= '
            Microsoft.Maps.Events.addHandler(pin_'.$pin_id.', "click", function(event){
                '.$pin['click'].'
            });
            ';
        }
        
        if($pin['dblclick'] != '') {
            $output .= '
            Microsoft.Maps.Events.addHandler(pin_'.$pin_id.', "dblclick", function(event){
                '.$pin['dblclick'].'
            });
            ';
        }
        
        if($pin['draggable']) {
        
            if($pin['drag'] != '') {
                $output .= '
                Microsoft.Maps.Events.addHandler(pin_'.$pin_id.', "drag", function(event){
                    '.$pin['drag'].'
                });
                ';
            }

            if($pin['dragend'] != '') {
                $output .= '
                Microsoft.Maps.Events.addHandler(pin_'.$pin_id.', "dragend", function(event){
                    '.$pin['dragend'].'
                });
                ';
            }

            if($pin['dragstart'] != '') {
                $output .= '
                Microsoft.Maps.Events.addHandler(pin_'.$pin_id.', "dragstart", function(event){
                    '.$pin['dragstart'].'
                });
                ';
            }
            
        }
        
        if($pin['mousedown'] != '') {
            $output .= '
            Microsoft.Maps.Events.addHandler(pin_'.$pin_id.', "mousedown", function(event){
                '.$pin['mousedown'].'
            });
            ';
        }
        
        if($pin['mouseout'] != '') {
            $output .= '
            Microsoft.Maps.Events.addHandler(pin_'.$pin_id.', "mouseout", function(event){
                '.$pin['mouseout'].'
            });
            ';
        }
        
        if($pin['mouseover'] != '') {
            $output .= '
            Microsoft.Maps.Events.addHandler(pin_'.$pin_id.', "mouseover", function(event){
                '.$pin['mouseover'].'
            });
            ';
        }
        
        if($pin['mouseup'] != '') {
            $output .= '
            Microsoft.Maps.Events.addHandler(pin_'.$pin_id.', "mouseup", function(event){
                '.$pin['mouseup'].'
            });
            ';
        }
        
        if($pin['rightclick'] != '') {
            $output .= '
            Microsoft.Maps.Events.addHandler(pin_'.$pin_id.', "rightclick", function(event){
                '.$pin['rightclick'].'
            });
            ';
        }
        
        $this->pinsInfo['pin_'.$pin_id] = $pinInfo;
        
        array_push($this->pins, $output);
        
    }
    
    function create_map() {
        
        $this->output_js = '';
        $this->output_js_content = '';
        $this->output_html = '';
        
        if($this->https) {
            $api = 'https://ecn.dev.virtualearth.net/mapcontrol/mapcontrol.ashx?v=7.0&s=1';
        } else {
            $api = 'http://ecn.dev.virtualearth.net/mapcontrol/mapcontrol.ashx?v=7.0';
        }
        
        if($this->language != '') $api .= '&mkt='.$this->language;
        
        $this->output_js .= '<script type="text/javascript" charset="UTF-8" src="'.$api.'"></script>';
        
        $this->output_js_content .= '<script type="text/javascript">
            var '.$this->map_name.';
            var iw;
            var markers =  new Array();
            var dataLayer;
            var dataInfo;
            
            function GetMap() {
                var mapOptions = {
                    credentials: "'.$this->credentials.'",
                    zoom: '.$this->zoom.',
                    center: new Microsoft.Maps.Location('.$this->center.'),
                    mapTypeId: Microsoft.Maps.MapTypeId.'.$this->map_type;
        
        if($this->backgroundColor != '') {
            $this->output_js_content .= ',
                backgroundColor: \''.$this->backgroundColor.'\'';
        }
        
        if($this->customizeOverlays) {
            $this->output_js_content .= ',
                customizeOverlays: true';
        }
        
        if($this->disableBirdseye) {
            $this->output_js_content .= ',
                disableBirdseye: true';
        }
        
        if($this->disableKeyboardInput) {
            $this->output_js_content .= ',
                disableKeyboardInput: true';
        }
        
        if($this->disableMouseInput) {
            $this->output_js_content .= ',
                disableMouseInput: true';
        }
        
        if($this->disablePanning) {
            $this->output_js_content .= ',
                disablePanning: true';
        }
        
        if($this->disableTouchInput) {
            $this->output_js_content .= ',
                disableTouchInput: true';
        }
        
        if($this->disableUserInput) {
            $this->output_js_content .= ',
                disableUserInput: true';
        }
        
        if($this->disableZooming) {
            $this->output_js_content .= ',
                disableZooming: true';
        }
        
        if(!$this->enableClickableLogo) {
            $this->output_js_content .= ',
                enableClickableLogo: false';
        }
        
        if(!$this->enableSearchLogo) {
            $this->output_js_content .= ',
                enableSearchLogo: false';
        }
        
        if(!$this->fixedMapPosition) {
            $this->output_js_content .= ',
                fixedMapPosition: false';
        }
        
        if($this->inertiaIntensity != '') {
            $this->output_js_content .= ',
                inertiaIntensity: '.$this->inertiaIntensity;
        }
        
        if($this->showBreadcrumb) {
            $this->output_js_content .= ',
                showBreadcrumb: true';
        }
        
        if(!$this->showCopyright) {
            $this->output_js_content .= ',
                showCopyright: false';
        }
        
        if(!$this->showDashboard) {
            $this->output_js_content .= ',
                showDashboard: false';
        }
        
        if(!$this->showMapTypeSeector) {
            $this->output_js_content .= ',
                showMapTypeSelector: false';
        }
        
        if(!$this->showScalebar) {
            $this->output_js_content .= ',
                showScalebar: false';
        }
        
        if($this->theme != '') {
            $this->output_js_content .= ',
                theme: '.$this->theme;
        }
        
        if($this->tileBuffer != '') {
            $this->output_js_content .= ',
                tileBuffer: '.$this->tileBuffer;
        }
        
        if(!$this->useInertia) {
            $this->output_js_content .= ',
                useInertia: false';
        }
        
        if(!$this->animate) {
            $this->output_js_content .= ',
                animate: false';
        }
        
        if($this->bounds != '') {
            $this->output_js_content .= ',
                bounds: Microsoft.Maps.LocationRect'.$this->bounds;
        }
        
        if($this->centerOffset != '') {
            $this->output_js_content .= ',
                centerOffset: new Microsoft.Maps.Point('.$this->centerOffset.')';
        }
           
        if($this->heading != '') {
            $this->output_js_content .= ',
                heading: '.$this->heading;
        }
           
        if($this->labelOverlay != '') {
            $this->output_js_content .= ',
                labelOverlay: Microsoft.Maps.LabelOverlay.'.$this->labelOverlay;
        }
           
        if($this->padding != '') {
            $this->output_js_content .= ',
                padding: '.$this->padding;
        }
            
        $this->output_js_content .= '
                }
                
                '.$this->map_name.' = new Microsoft.Maps.Map(document.getElementById("'.$this->map_div_id.'"),mapOptions);
                ';
        
        if ($this->onclick_bing!="") { 
                $this->output_js_content .= 'Microsoft.Maps.Events.addHandler('.$this->map_name.', "click", function(event) {
                '.$this->onclick_bing.'
                });
                ';
        }
        
        $this->output_js_content .= '  
                
                dataLayer = new Microsoft.Maps.EntityCollection();
                dataInfo = new Microsoft.Maps.EntityCollection();
                
                '.$this->map_name.'.entities.push(dataLayer);
                '.$this->map_name.'.entities.push(dataInfo);
                
                AddData();
            }
            
            function InfoboxHandler(){
                alert("Infobox title was clicked!")
            }
            
            function AddData() {
            ';
        
        
        if(count($this->pins)) {
            foreach($this->pins as $pin) {
                $this->output_js_content .= $pin;
            }
        }
        
        $this->output_js_content .= '}
            
            function createMarker(markerOptions) {
                var marker_flag = checkMarkerCount();
                var marker_delete_flag = checkMarkerDeleteFlag();

                if(marker_flag == 0) {
                var marker = new Microsoft.Maps.Pushpin(markerOptions.position,{icon:markerOptions.icon});
                markerOptions.map.entities.push(marker)
                markers.push(marker);
                //lat_longs.push(marker.getPosition());
                
                if(marker_delete_flag) {
                
                 Microsoft.Maps.Events.addHandler(marker, "rightclick", function(e) {
                  deleteMarker(markerOptions.position.latitude, markerOptions.position.longitude);
                  markerOptions.map.entities.clear();
                 });
                }
                
               }
                return marker;
            }
            
            window.onload = GetMap();
            </script>
            ';
        
        $this->output_js .= $this->output_js_content;
        $this->output_html .= '<div id="'.$this->map_div_id.'" style="position:relative;width:'.$this->map_width.'; height:'.$this->map_height.';" ></div>';
        
        return array('js' => $this->output_js, 'html' => $this->output_html, 'credentials' => $this->credentials);
        
    }
    
    function is_lat_long($input)
    {

            $input = str_replace(", ", ",", trim($input));
            $input = explode(",", $input);
            if (count($input)==2) {

                    if (is_numeric($input[0]) && is_numeric($input[1])) { // is a lat long
                            return true;
                    }else{ // not a lat long - incorrect values
                            return false;
                    }

            }else{ // not a lat long - too many parts
                    return false;
            }

    }
}