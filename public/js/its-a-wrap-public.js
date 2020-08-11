jQuery(document).on('click', '.itsaWrap-share-popup', function(){
  jQuery('.itsaWrap-share-popuptext').removeClass('show');
  jQuery(this).children('span').toggleClass('show');
});

jQuery('html').click(function(e) {                    
  if(!jQuery(e.target).hasClass('itsaWrap-share-popup') )
  {
    jQuery('.itsaWrap-share-popuptext').removeClass('show');               
  }
});