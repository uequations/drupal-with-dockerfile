<?php 
if(!class_exists('element_gva_progress_work_list')):
   class element_gva_progress_work_list{
      public function render_form(){
         $fields = array(
            'type'      => 'gva_progress_work_list',
            'title'  => t('Progress Work List'), 
            'fields' => array(
               array(
                  'id'     => 'title',
                  'type'   => 'text',
                  'title'  => t('Title'),
                  'admin'  => true
               ),
               array(
                  'id'     => 'el_class',
                  'type'      => 'text',
                  'title'  => t('Extra class name'),
                  'desc'      => t('Style particular content element differently - add a class name and refer to it in custom CSS.'),
               ),
              array(
                  'id'        => 'animate',
                  'type'      => 'select',
                  'title'     => t('Animation'),
                  'desc'      => t('Entrance animation for element'),
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
               ), 
            ),                                           
         );

         gavias_carousel_fields_settings($fields);

         for($i=1; $i<=8; $i++){
            $fields['fields'][] = array(
               'id'     => "info_{$i}",
               'type'   => 'info',
               'desc'   => "Information for item {$i}"
            );
            $fields['fields'][] = array(
               'id'        => "title_{$i}",
               'type'      => 'text',
               'title'     => t("Title {$i}")
            );
            $fields['fields'][] = array(
               'id'        => "icon_{$i}",
               'type'      => 'text',
               'title'     => t("Icon {$i}"),
               'class'     => 'width-1-2'
            );
            $fields['fields'][] = array(
               'id'        => "number_{$i}",
               'type'      => 'text',
               'title'     => t("Number {$i}"),
               'class'     => 'width-1-2'
            );
            $fields['fields'][] = array(
               'id'           => "content_{$i}",
               'type'         => 'textarea_without_html',
               'title'        => t("Content {$i}")
            );
            
         }
      return $fields;
      }

      public static function render_content( $attr = array(), $content = '' ){
         $default = array(
            'title'           => '',
            'el_class'        => '',
            'animate'         => '',
            'animate_delay'   => '',
            'col_lg'          => '4',
            'col_md'          => '3',
            'col_sm'          => '2',
            'col_xs'          => '1',
            'auto_play'       => '0',
            'pagination'      => '0',
            'navigation'      => '0'
         );        
         for($i=1; $i<=8; $i++){
            $default["title_{$i}"] = '';
            $default["icon_{$i}"] = '';
            $default["number_{$i}"] = '';
            $default["content_{$i}"] = '';
         }
         extract(gavias_merge_atts($default, $attr)); 

         $classes = array();
         $classes[] = $el_class;
         if($animate){
            $classes[] = 'wow'; 
            $classes[] = $animate; 
         }  
         ob_start();
         ?>

      <div class="gsc-workprocess-list <?php echo implode(' ', $classes) ?>"> 
         <div class="owl-carousel init-carousel-owl owl-loaded owl-drag" data-items="<?php print $col_lg ?>" data-items_lg="<?php print $col_lg ?>" data-items_md="<?php print $col_md ?>" data-items_sm="<?php print $col_sm ?>" data-items_xs="<?php print $col_xs ?>" data-loop="1" data-speed="500" data-auto_play="<?php print $auto_play ?>" data-auto_play_speed="2000" data-auto_play_timeout="5000" data-auto_play_hover="1" data-navigation="<?php print $navigation ?>" data-rewind_nav="0" data-pagination="<?php print $pagination ?>" data-mouse_drag="1" data-touch_drag="1">
            <?php for($i=1; $i<=8; $i++){ ?>
                  <?php 
                     $title = "title_{$i}";
                     $icon = "icon_{$i}";
                     $number = "number_{$i}";
                     $content = "content_{$i}";
                  ?>


               <?php if($$title){ ?>
                  <div class="item">
                     <div class="workprocess-one__single <?php echo implode(' ', $classes) ?>">
                        <div class="workprocess-one__top">
                           <?php if($$number){?>
                             <div class="workprocess-one__number"><?php print $$number ?></div>
                           <?php } ?>
                           <?php if($$icon){?>
                             <div class="workprocess-one__icon">
                                 <i class="<?php print $$icon ?>"></i>
                              </div>
                           <?php } ?>
                        </div>
                        <div class="workprocess-one__content">
                           <?php
                              if($$title){
                                 print '<h3 class="workprocess-one__title">' .  $$title . '</h3>';
                              }  
                           ?>
                           <?php
                              if($$content){
                                 print '<div class="workprocess-one__desc">' .  $$content . '</div>';
                              }  
                           ?>
                        </div>
                     </div> 
                  </div>
               <?php } ?>   
            <?php } ?>
         </div>   
      </div> 

         <?php return ob_get_clean();
      }

   }
 endif;  
