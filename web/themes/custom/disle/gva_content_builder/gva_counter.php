<?php 

if(!class_exists('element_gva_counter')):
   class element_gva_counter{
      public function render_form(){
         $fields = array(
            'type' => 'element_gva_counter',
            'title' => ('Counter'),
            'fields' => array(
               array(
                  'id'        => 'title',
                  'title'     => t('Title'),
                  'type'      => 'text',
                  'admin'     => true,
                  'class'     => 'width-1-2'
               ),
               array(
                  'id'        => 'icon',
                  'title'     => t('Icon'),
                  'type'      => 'text',
                  'std'       => '',
                  'desc'     => t('Use class icon font <a target="_blank" href="http://fontawesome.io/icons/">Icon Awesome</a> or <a target="_blank" href="http://gaviasthemes.com/icons/icons-2/">Custom icon</a>'),
                  'class'     => 'width-1-2'
               ),
               array(
                  'id'        => 'number',
                  'title'     => t('Number'),
                  'type'      => 'text',
                  'class'     => 'width-1-2'
               ),
               array(
                  'id'        => 'symbol',
                  'title'     => t('Symbol'),
                  'type'      => 'text',
                  'class'     => 'width-1-2'
               ),
               array(
                  'id'        => 'style',
                  'title'     => t('Style'),
                  'type'      => 'select',
                  'options'   => array(
                     'style-1'          => 'Style I',
                     'style-2'          => 'Style II',
                     'style-3'          => 'Style III',
                  ),
                  'class'     => 'width-1-2'
               ),
               
               array(
                  'id'        => 'style_text',
                  'type'      => 'select',
                  'title'     => t('Skin Text for box'),
                  'options'   => array(
                     'text-dark'   => 'Text dark',
                     'text-light'   => 'Text light'
                  ),
                  'class'     => 'width-1-2'
               ),
               array(
                  'id'        => 'el_class',
                  'type'      => 'text',
                  'title'     => t('Extra class name'),
                  'desc'      => t('Style particular content element differently - add a class name and refer to it in custom CSS.'),
                  'class'     => 'width-1-3'
               ),
               array(
                  'id'        => 'animate',
                  'type'      => 'select',
                  'title'     => t('Animation'),
                  'desc'      => t('Entrance animation for element'),
                  'options'   => gavias_content_builder_animate(),
                  'class'     => 'width-1-3',

               ), 
               array(
                  'id'        => 'animate_delay',
                  'type'      => 'select',
                  'title'     => t('Animation Delay'),
                  'options'   => gavias_content_builder_delay_wow(),
                  'desc'      => '0 = default',
                  'class'     => 'width-1-3'
               ), 
         
            ),                                      
         );
         return $fields;
      }


      public function render_content( $attr = array(), $content = '' ){
         extract(gavias_merge_atts(array(
            'title'         => '',
            'icon'          => '',
            'number'        => '',
            'symbol'        => '',
            'style'         => 'style-1',
            'el_class'      => '',
            'style_text'    => 'text-dark',
            'animate'       => '',
            'animate_delay' => ''
         ), $attr));
         $class = array();
         $class[] = $el_class;
         $class[] = $style_text;
         if($animate) $class[] = 'wow ' . $animate; 
         ob_start();
      ?>

         <?php if($style == 'style-1'){ ?>
            <div class="widget milestone-block milestone-one__single <?php if(count($class) > 0){ print implode(' ', $class); } ?>" <?php print gavias_content_builder_print_animate_wow('', $animate_delay) ?>>
               <div class="milestone-one__content">
                  <?php if($icon){ ?>
                     <div class="milestone-one__icon"><span class="icon"><i class="<?php print $icon; ?>"></i></span></div>
                  <?php } ?>   
                  <div class="milestone-one__number-inner">
                     <span class="milestone-one__number milestone-number"><?php print $number; ?></span>
                     <?php if($symbol){ ?>
                        <span class="milestone-one__symbol"><?php print $symbol; ?></span>
                     <?php } ?>
                  </div>
                  <h3 class="milestone-one__text"><?php print $title ?></h3>
               </div>
            </div>
         <?php } ?>  

         <?php if($style == 'style-2'){ ?>
            <div class="widget milestone-block milestone-two__single <?php if(count($class) > 0){ print implode(' ', $class); } ?>" <?php print gavias_content_builder_print_animate_wow('', $animate_delay) ?>>
               <div class="milestone-two__content">
                  <?php if($icon){ ?>
                     <div class="milestone-two__icon"><span class="icon"><i class="<?php print $icon; ?>"></i></span></div>
                  <?php } ?>   
                  <div class="milestone-two__content-inner">
                     <div class="milestone-two__number-inner">
                        <span class="milestone-two__number milestone-number"><?php print $number; ?></span>
                        <?php if($symbol){ ?>
                           <span class="milestone-two__symbol"><?php print $symbol; ?></span>
                        <?php } ?>
                     </div>
                     <h3 class="milestone-two__text"><?php print $title ?></h3>
                  </div>
               </div>
            </div>
         <?php } ?>  

         <?php if($style == 'style-3'){ ?>
            <div class="widget milestone-block milestone-three__single <?php if(count($class) > 0){ print implode(' ', $class); } ?>" <?php print gavias_content_builder_print_animate_wow('', $animate_delay) ?>>
               <div class="milestone-three__wrap">
                  <?php if($icon){ ?>
                     <div class="milestone-three__icon"><span class="icon"><i class="<?php print $icon; ?>"></i></span></div>
                  <?php } ?>
                  <div class="milestone-three__content">
                     <div class="milestone-three__number-inner">
                        <span class="milestone-three__number milestone-number"><?php print $number; ?></span>
                        <?php if($symbol){ ?>
                           <span class="milestone-three__symbol"><?php print $symbol; ?></span>
                        <?php } ?>
                     </div>
                     <h3 class="milestone-three__text"><?php print $title ?></h3>
                  </div>
               </div>   
            </div>
         <?php } ?>  
          
         <?php return ob_get_clean() ?>
         <?php
      }

   }
endif;
   



