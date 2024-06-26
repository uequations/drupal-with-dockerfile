<?php 
if(!class_exists('element_gva_service_block')):
   class element_gva_service_block{
      public function render_form(){
         $fields = array(
            'type' => 'element_gva_service_block',
            'title' => t('Service Block'),
            'fields' => array(
               array(
                  'id'        => 'title',
                  'type'      => 'text',
                  'title'     => t('Title'),
                  'admin'     => true,
                  'default'   => 'Manage IT services',
                  'class'     => 'width-1-2'
               ),
               array(
                  'id'        => "number",
                  'type'      => 'text',
                  'title'     => t("Number"),
                  'class'     => 'width-1-2',
               ),
               array(
                  'id'        => "icon",
                  'type'      => 'text',
                  'title'     => t("Icon"),
                  'default'   => 'flaticon-rating',
                  'class'     => 'width-1-2'
               ),
               array(
                  'id'        => "link",
                  'type'      => 'text',
                  'title'     => t("Link"),
                  'class'     => 'width-1-2'
               ),
               array(
                  'id'           => "content",
                  'type'         => 'text',
                  'title'        => t("Content"),
                  'default'      => ''
               ),
               array(
                  'id'        => 'style',
                  'type'      => 'select',
                  'title'     => t('Style'),
                  'options'   => array(
                     'style-1'   => 'Style 01',
                     'style-2'   => 'Style 02',
                     'style-3'   => 'Style 03'
                  ),
                  'class'     => 'width-1-4'
               ),
               array(
                  'id'        => 'el_class',
                  'type'      => 'text',
                  'title'     => t('Extra Class Name'),
                  'class'     => 'width-1-4'
               ),
               array(
                  'id'        => 'animate',
                  'type'      => 'select',
                  'title'     => t('Animation'),
                  'desc'      => t('Entrance animation for element'),
                  'options'   => gavias_content_builder_animate(),
                  'class'     => 'width-1-4'
               ), 
               array(
                  'id'        => 'animate_delay',
                  'type'      => 'select',
                  'title'     => t('Animation Delay'),
                  'options'   => gavias_content_builder_delay_wow(),
                  'desc'      => '0 = default',
                  'class'     => 'width-1-4'
               ),  
            ),                                     
         );
         return $fields;
      }

      public static function render_content( $attr = array(), $content = '' ){
         global $base_url;
         $default = array(
            'title'           => '',
            'style'           => 'style-1',
            'icon'            => '',
            'number'           => '',
            'content'         => '',
            'link'            => '',
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

         extract(gavias_merge_atts($default, $attr));

         $classes = array();
         $classes[] = $el_class;
         $classes[] = $style;
         if($animate){
            $classes[] = 'wow'; 
            $classes[] = $animate; 
         }  
         $link = gavias_get_uri($link);
         ob_start();
      ?>
         
      <div class="el-service-block <?php echo implode(' ', $classes) ?>"> 
         <?php if($style == 'style-1'){ ?>
            <div class="service-one__single">
               <div class="service-one__content">
                  <div class="service-one__icon-inner">
                  <?php 
                     if($icon){
                        print '<div class="service-one__icon"><i class="' . $icon . '"></i></div>';
                     } 
                     if($number){
                        print '<div class="service-one__number">' .  $number . '</div>';
                     }   
                  ?>
                  </div>
                  <div class="service-one__content-inner">
                     <?php 
                        if($title){
                           print '<h3 class="service-one__title">' .  $title . '</h3>';
                        } 
                        if($content){ print '<div class="service-one__desc">' . $content . '</div>'; } 
                     ?>
                  </div>
               </div>
               <?php
                  if($link){ 
                     print '<a class="service-one__link" href="' . $link . '"></a>';
                  } 
               ?>
            </div>
         <?php } ?>   

         <?php if($style == 'style-2'){ ?>
            <div class="service-two__single">
               <div class="service-two__content">
                  <?php 
                     if($icon){
                        print '<div class="service-two__icon"><i class="' . $icon . '"></i></div>';
                     } 
                     if($number){
                        print '<div class="service-two__number">' .  $number . '</div>';
                     }   
                  ?>
               
                  <div class="service-two__content-inner">
                     <?php 
                        if($title){
                           print '<h3 class="service-two__title">' .  $title . '</h3>';
                        } 
                        if($content){ print '<div class="service-two__desc">' . $content . '</div>'; } 
                     ?>
                  </div>
               </div>
               <?php
                  if($link){ 
                     print '<a class="service-two__overlay-link" href="' . $link . '"></a>';
                  } 
               ?>
            </div>
         <?php } ?> 
         <?php if($style == 'style-3'){ ?>
            <div class="service-three__single">
               <div class="service-three__content">
                  <?php 
                     if($icon){
                        print '<div class="service-three__icon"><i class="' . $icon . '"></i></div>';
                     } 
                     if($number){
                        print '<div class="service-three__number">' .  $number . '</div>';
                     }   
                  ?>
               
                  <div class="service-three__content-inner">
                     <?php 
                        if($title){
                           print '<h3 class="service-three__title">' .  $title . '</h3>';
                        } 
                        if($content){ print '<div class="service-three__desc">' . $content . '</div>'; } 
                        if($link){ 
                           print '<div class="service-three__action"><a class="btn-black" href="' . $link . '"><i class="fa fa-long-arrow-alt-right"></i></a></div>';
                        }
                     ?>
                  </div>
               </div>
               <?php
                  if($link){ 
                     print '<a class="service-three__overlay-link" href="' . $link . '"></a>';
                  } 
               ?>
            </div>
         <?php } ?> 

      </div> 

      <?php return ob_get_clean();
      }
   }
endif;

