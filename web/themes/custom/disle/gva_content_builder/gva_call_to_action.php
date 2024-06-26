<?php 
if(!class_exists('element_gva_call_to_action')):
   class element_gva_call_to_action{
      public function render_form(){
         $fields = array(
            'type' => 'gsc_call_to_action',
            'title' => t('Call to Action'),
            'size' => 12,
            
            'fields' => array(
               array(
                  'id'        => 'title',
                  'type'      => 'text',
                  'title'     => t('Title'),
                  'admin'     => true
               ),
               array(
                  'id'        => 'sub_title',
                  'type'      => 'text',
                  'title'     => t('Sub Title'),
               ),
               array(
                  'id'        => 'content',
                  'type'      => 'textarea',
                  'title'     => t('Content'),
                  'desc'      => t('HTML tags allowed.'),
               ),
               array(
                  'id'        => 'link',
                  'type'      => 'text',
                  'title'     => t('Link'),
               ),
               array(
                  'id'        => 'button_title',
                  'type'      => 'text',
                  'title'     => t('Button Title'),
                  'desc'      => t('Leave this field blank if you want Call to Action with Big Icon'),
               ),
               array(
                  'id'           => 'button_align',
                  'type'         => 'select',
                  'title'        => 'Style',
                  'options'      => array(
                     'button-center'       => t('Button Center'),
                     'button-right'        => t('Button Right I'),
                     'button-right-2'        => t('Button Right II'),
                     'button-right-3'        => t('Button Right III'),
                  ),
               ),
               array(
                  'id'        => 'width',
                  'type'      => 'text',
                  'title'     => t('Max width for content'),
                  'default'   => '500px',
                  'desc'      => 'e.g 660px'
               ),
               array(
                  'id'        => 'style_text',
                  'type'      => 'select',
                  'title'     => 'Skin Text for box',
                  'options'   => array(
                        'text-light'  => 'Text light',
                        'text-dark'   => 'Text dark',
                  ),
                  'std'       => 'text-dark'
               ),
               array(
                  'id'        => 'style_button',
                  'type'      => 'select',
                  'title'     => 'Style button',
                  'options'   => array(
                        'btn-theme'          => 'Button default of theme',
                        'btn-theme-second'   => 'Button Second of theme',
                        'btn-white'          => 'Button white',
                        'btn-black'          => 'Button Black' 
                  ),
                  'std'       => 'text-dark'
               ),
               array(
                  'id'        => 'target',
                  'type'      => 'select',
                  'title'     => t('Open in new window'),
                  'desc'      => t('Adds a target="_blank" attribute to the link'),
                  'options'   => array( 'off' => 'Off', 'on' => 'On' ),
               ),
               array(
                  'id'        => 'el_class',
                  'type'      => 'text',
                  'title'     => t('Extra class name'),
                  'desc'      => t('Style particular content element differently - add a class name and refer to it in custom CSS.'),
               ),
               array(
                  'id'        => 'animate',
                  'type'      => 'select',
                  'title'     => t('Animation'),
                  'sub_desc'  => t('Entrance animation'),
                  'options'   => gavias_content_builder_animate(),
                  'class'     => 'width-1-2'
               ),
               array(
                  'id'        => 'animate_delay',
                  'type'      => 'select',
                  'title'     => t('Animation Delay'),
                  'options'   => gavias_content_builder_delay_aos(),
                  'desc'      => '0 = default',
                  'class'     => 'width-1-2'
               )
            )                                       
         );
      return $fields;
      }

      function render_content( $attr = array(), $content = '' ){
         extract(gavias_merge_atts(array(
            'title'           => '',
            'sub_title'       => '',
            'content'         => '',
            'link'            => '',
            'button_title'    => '',
            'button_align'    => '',
            'width'           => '',
            'style_button'    => 'btn-theme',
            'target'          => '',
            'el_class'        => '',
            'animate'         => '',
            'animate_delay'   => '',
            'style_text'      => 'text-dark',
         ), $attr));
         
         // target
         if( $target =='on' ){
            $target = 'target="_blank"';
         } else {
            $target = false;
         }
         
         $class = array();
         $class[] = $el_class;
         $class[] = $button_align;
         $class[] = $style_text;
        
         $width_style =  'style="max-width:'. $width .'"';
         if($animate) $class[] = ' wow ' . $animate;
         
         $link = gavias_get_uri($link);
          ob_start();
         ?>
         <?php if($button_align == 'button-center'){ ?>
         <div class="widget call-to-action-one__single <?php print implode(' ', $class) ?>" <?php print gavias_content_builder_print_animate_wow('', $animate_delay) ?>>
            <div class="call-to-action-one__content-inner" <?php print $width_style ?>>
               <div class="call-to-action-one__content">
                  <?php if($sub_title){?><div class="call-to-action-one__sub-title"><?php print $sub_title; ?></div><?php } ?>
                  <?php if($title){?><h2 class="call-to-action-one__title"><span><?php print $title; ?></span></h2><?php } ?>
                  <?php if($content){?><div class="call-to-action-one__desc"><?php print $content; ?></div><?php } ?>
               </div>
               <?php if($link){?>
               <div class="call-to-action-one__action">
                  <a href="<?php print $link ?>" class="<?php print $style_button ?>" <?php print $target ?>>
                     <span><?php print $button_title ?></span>
                  </a>   
               </div>
               <?php } ?>
            </div>
         </div>
         <?php } ?>
         <?php if($button_align == 'button-right'){ ?>
         <div class="widget call-to-action-two__single <?php print implode(' ', $class) ?>" <?php print gavias_content_builder_print_animate_wow('', $animate_delay) ?>>
            <div class="call-to-action-two__content-inner" >
               <div class="call-to-action-two__content" <?php print $width_style ?>>
                  <?php if($sub_title){?><div class="call-to-action-two__sub-title"><?php print $sub_title; ?></div><?php } ?>
                  <?php if($title){?><h2 class="call-to-action-two__title"><span><?php print $title; ?></span></h2><?php } ?>
                  <?php if($content){?><div class="call-to-action-two__desc"><?php print $content; ?></div><?php } ?>
               </div>
               <?php if($link){?>
               <div class="call-to-action-two__action">
                  <a href="<?php print $link ?>" class="<?php print $style_button ?>" <?php print $target ?>>
                     <span><?php print $button_title ?></span>
                  </a>   
               </div>
               <?php } ?>
            </div>
         </div>
         <?php } ?>
         <?php if($button_align == 'button-right-2'){ ?>
         <div class="widget call-to-action-three__single <?php print implode(' ', $class) ?>" <?php print gavias_content_builder_print_animate_wow('', $animate_delay) ?>>
            <div class="call-to-action-three__content-inner" >
               <div class="call-to-action-three__content" <?php print $width_style ?>>
                  <?php if($sub_title){?><div class="call-to-action-three__sub-title"><?php print $sub_title; ?></div><?php } ?>
                  <?php if($title){?><h2 class="call-to-action-three__title"><span><?php print $title; ?></span></h2><?php } ?>
                  <?php if($content){?><div class="call-to-action-three__desc"><?php print $content; ?></div><?php } ?>
               </div>
               <?php if($link){?>
               <div class="call-to-action-three__action">
                  <a href="<?php print $link ?>" class="<?php print $style_button ?>" <?php print $target ?>>
                     <span><?php print $button_title ?></span>
                  </a>   
               </div>
               <?php } ?>
            </div>
         </div>
         <?php } ?>
         <?php if($button_align == 'button-right-3'){ ?>
         <div class="widget call-to-action-four__single <?php print implode(' ', $class) ?>" <?php print gavias_content_builder_print_animate_wow('', $animate_delay) ?>>
            <div class="call-to-action-four__content-inner" >
               <div class="call-to-action-four__content" <?php print $width_style ?>>
                  <?php if($sub_title){?><div class="call-to-action-four__sub-title"><?php print $sub_title; ?></div><?php } ?>
                  <?php if($title){?><h2 class="call-to-action-four__title"><span><?php print $title; ?></span></h2><?php } ?>
                  <?php if($content){?><div class="call-to-action-four__desc"><?php print $content; ?></div><?php } ?>
               </div>
               <?php if($link){?>
               <div class="call-to-action-four__action">
                  <a href="<?php print $link ?>" class="<?php print $style_button ?>" <?php print $target ?>>
                     <span><?php print $button_title ?></span>
                  </a>   
               </div>
               <?php } ?>
            </div>
         </div>
         <?php } ?>


         <?php return ob_get_clean() ?>
      <?php
      }
   }
endif;   



