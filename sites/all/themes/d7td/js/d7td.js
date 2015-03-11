(function($) {

  // Activate the wonky april fool's prank.
  // Behavior name (e.g., "d7td_wonky") needs to be unique.
  Drupal.behaviors.d7td_wonky = {
    attach: function d7td_wonky() {
      $(document).ready(function d7td_document_ready_fool() { //  When the document is ready
	$.fool('wonky');
      });
    }
  }
  
})(jQuery);
