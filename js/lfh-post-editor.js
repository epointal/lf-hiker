

 (function($) {
  if( typeof lfh == 'undefined'){
      lfh = {}
  }
  if( typeof lfh.is_initialised == 'undefined'){
      lfh.is_initialised = new Array();
  }
  /**
   * IMPORTANT Must be loaded before the fields lfh_color then in header
   * It can be great to write it directly in line @see views/admin/select-color-path.phtml
   */
  // the field lfh_color is sometimes loaded after the page (ajax)
  // so only create the function for init and call it after write the html for this field 
  
  lfh.init_dom_color = function( node_id ){
      if( typeof lfh.is_initialised[node_id] == 'undefined'){
          //lfh.is_initialised[node_id] = true;
          //trigger change on the input for save value with 'wp_ajax_save-attachment-compat' 
          $('#'+node_id+' input').trigger('change');
          $('#'+node_id+' .lfh-color-option.to-extend div').click(function(){
              console.log('click');
              var parent = $(this).parent().parent();
              color = $(this).data('value');
              parent.find('div.lfh-color-chosen').css('background-color', color);
              parent.children('input').val(color);
              parent.find('.to-extend div.selected').removeClass('selected');
              $(this).addClass('selected');
              //trigger change on the input for save value with 'wp_ajax_save-attachment-compat'
              $('#'+node_id+' input').trigger('change');
          });
         // $('.lfh-color-chosen').click(function(){
            //  $(this).parent().parent().children('.to-extend').toggleClass('hidden');
          //});
      }
      $('#'+node_id +' .lfh-color-chosen').parent().parent().children('.to-extend').toggleClass('hidden');
  }
   $(document).ready(function(){
            $('#insert-gpx').click(open_media_window);
          
  });
  function open_media_window(event) {
    console.log('open media');
    event.preventDefault();
    var wmedia = wp.media({
            title: 'Insert a media',
            library: {type: 'image'},
            multiple: false,
            button: {text: 'Insert'}
        });
}
    

})(jQuery);