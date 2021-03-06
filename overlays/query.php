<?php

/**
 * Overlay to record queries posted by surfer (contact) 
 * This overlay enable anonymous surfer to receive mail even if
 * he is not a member.
 * 
 * @see /query.php 
 * 
 * @author Alexis Raimbault
 * @reference
 * @license http://www.gnu.org/copyleft/lesser.txt GNU Lesser General Public License
 */

Class query extends Overlay {
    
    public function get_view_text($host = NULL) {
       $text = '';
       
       return $text;
    }
    
    public function should_notify_watchers($mail = NULL) {
       
        $target = $this->anchor->item['create_address'];
        
        if($target == $_REQUEST['create_address']) return true;
        
        // send email to anonymous surfer;
        Mailer::notify(null, $target , $mail['subject'], $mail['notification'], $mail['headers']);
        
       // allow normal processing
       return true;
    }
    
}