<?php
/**
 * 2 columns  layout with parallax effect
 * @see layouts/layout.php
 */
class Layout_as_2col_parallax extends Layout_interface {

    // Suppression du constructeur explicite car déjà géré par Layout_interface
    // function _construct() {
    //     parent::_construct();
    //     $this->load_scripts_n_styles();
    // }

    function items_per_page() {
        // Permet de surcharger via variant (ex: layout="as_2col_parallax per_page_8")
        if($nb = $this->has_variant('per_page'))
            return (int)$nb;
        return 12; // Optimisé pour l'effet parallaxe
    }

    function layout($result) {
        global $context;

        // Chargement des assets via la méthode parente
        $this->load_scripts_n_styles();

        if(!SQL::count($result)) 
            return '';
        
        // Initialisation
        $text = '<div class="yacs-parallax" data-layout="2col">';
        $count = 0;

        while($item = SQL::fetch($result)) {
            $entity = new $this->listed_type($item);
            $text .= $this->build_card($item, $entity, ++$count);
        }

        $text .= '</div>';
        SQL::free($result);
        return $text;
    }


    protected function build_card($item, $entity, $count) {
        $url = $entity->get_permalink();
        $class = ($count % 2) ? 'odd' : 'even';

        return '
        <article class="card '.$class.'" data-id="'.$item['id'].'">
            '.$this->build_header($item, $url).'
            '.$this->build_content($item, $entity).'
            '.$this->build_footer($url,$item['title']).'
        </article>';
    }

    protected function build_header($item, $url) {
        $header = '<header class="card-header">';
        
        // Thumbnail
        if(!empty($item['thumbnail_url'])) {
            $header .= Skin::build_link($url, 
                '<img src="'.$item['thumbnail_url'].'" alt="" loading="lazy">', 
                'basic');
        }
        
        // Title with flags
        $title = '';
        if (isset($item['activation_date']) && ($item['activation_date'] > gmdate('Y-m-d H:i:s'))) {
            $title .= DRAFT_FLAG;
        }
        if($item['active'] == 'N') $title .= PRIVATE_FLAG;
        elseif($item['active'] == 'R') $title .= RESTRICTED_FLAG;
        
        $title .= Codes::beautify_title($item['title']);
        
        $header .= '<h3>'.Skin::build_link($url, $title, 'basic').'</h3>';
        $header .= '</header>';
        
        return $header;
    }

    protected function build_content($item, $entity) {
        $content = '<div class="card-content">';
        
        // Introduction from overlay or direct
        $intro = is_object($entity->overlay)
            ? $entity->overlay->get_text('introduction', $item)
            : $item['introduction'];
        
        $content .= $intro ? '<p>'.strip_tags($intro).'</p>' : '';
        $content .= '</div>';
        
        return $content;
    }

    protected function build_footer($url,$title) {
        return '<footer class="card-footer">'
            .Skin::build_link($url, 'Lire les explications sur <strong> '. $title.'</strong>', 'button')
            .'</footer>';
    }
}