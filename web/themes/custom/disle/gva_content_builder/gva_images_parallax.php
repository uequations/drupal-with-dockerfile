<?php 

if(!class_exists('element_gva_images_parallax')):
	class element_gva_images_parallax{
		public function render_form(){
			return array(
			  'type'          => 'gva_images_parallax',
				'title'        => t('Images Style'),
				'fields' => array(
					array(
						'id'        => 'title',
						'type'      => 'text',
						'title'     => t('Title'),
						'admin'     => true
					),
					array(
						'id'        => 'image_1',
						'type'      => 'upload',
						'title'     => t('Images 1'),
						'class'		=> 'width-1-2'
					),
					array(
						'id'        => 'image_2',
						'type'      => 'upload',
						'title'     => t('Images 2'),
						'class'		=> 'width-1-2'
					),
				  array(
						'id'        => 'style',
						'type'      => 'select',
						'title'     => t('Images Style'),
						'options'   => array( 
							'style-1' => t('Images Style 1'), 
							'style-2' => t('Images Style 2'),
							'style-3' => t('Images Style 3')
						),
						'class'		=> 'width-1-3'
					),
					array(
						'id'        => 'link',
						'type'      => 'text',
						'title'     => t('Link Image'),
						'class'		=> 'width-1-3'
					),

					array(
						'id'        => 'target',
						'type'      => 'select',
						'title'     => t('Open in new window'),
						'desc'      => t('Adds a target="_blank" attribute to the link'),
						'options'   => array( 'off' => 'No', 'on' => 'Yes' ),
						'std'       => 'on',
						'class'		=> 'width-1-3'
					),

					array(
						'id'        => 'el_class',
						'type'      => 'text',
						'title'     => t('Extra class name'),
						'class'		=> 'width-1-3'
					),
					array(
						'id'        => 'animate',
						'type'      => 'select',
						'title'     => t('Animation'),
						'desc'      => t('Entrance animation for element'),
						'options'   => gavias_content_builder_animate(),
						'class'     => 'width-1-3'
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
		}

		public static function render_content( $attr = array(), $content = '' ){
			global $base_url;
			extract(gavias_merge_atts(array(
				'title'              => '',
				'icon'               => '',
				'image_1'            => '',
				'image_2'            => '',
				'link'               => '',
				'target'             => '',
				'style'              => 'style-1',
				'el_class'           => '',
				'animate'            => '',
				'animate_delay'      => ''
			), $attr));

			// target
			if( $target =='on' ){
				$target = ' target="_blank"';
			} else {
				$target = false;
			}

			if($image_1) $image_1 = $base_url . $image_1; 
			if($image_2) $image_2 = $base_url . $image_2; 

			
			if($style) $el_class .= ' ' . $style;
			if($animate) $el_class .= ' wow ' . $animate; 

			
			$link = gavias_get_uri($link);
			ob_start();
			?>
		<?php if($style == 'style-1'){ ?>
		  <div class="images-style-one__single <?php print $el_class; ?> ">
			 <div class="images-style-one__content">
				<div class="images-style-one__images">
				  	<?php 
					  	if($image_1){
						 	print '<div class="images-style-one__image-1">';
								if($link){ print '<a href="' . $link . '"' . $target . '>'; }
							  		print '<img src="' . $image_1 .'" alt="' . $title . '" />';
								if($link){ print '</a>'; } 
						 	print '</div>';  
					  	} 
					  	if($image_2){ 
					  		print '<div class="images-style-one__image-2">';
								if($link){ print '<a href="' . $link . '"' . $target . '>'; }
								  print '<img src="' . $image_2 .'" alt="' . $title . '" />';
								if($link){ print '</a>'; }
						 	print '</div>';  
					  	} 
				  ?> 

				</div>
			 </div>
		  </div>
		<?php } ?>

        <?php if($style == 'style-2'){ ?>
        	<div class="images-style-two__single <?php print $el_class; ?> ">
			 	<div class="images-style-two__content">
					<div class="images-style-two__images">
				  	<?php 
					  	if($image_1){
						 	print '<div class="images-style-two__image-1">';
								if($link){ print '<a href="' . $link . '"' . $target . '>'; }
							  		print '<img src="' . $image_1 .'" alt="' . $title . '" />';
								if($link){ print '</a>'; } 
						 	print '</div>';  
					  	} 
					  	if($image_2){ 
					  		print '<div class="images-style-two__image-2">';
								if($link){ print '<a href="' . $link . '"' . $target . '>'; }
								  print '<img src="' . $image_2 .'" alt="' . $title . '" />';
								if($link){ print '</a>'; }
						 	print '</div>';  
					  	} 
				  	?> 

					</div>
			 	</div>
		  	</div>
        <?php } ?>

        <?php if($style == 'style-3'){ ?>
        	<div class="images-style-three__single <?php print $el_class; ?> ">
			 	<div class="images-style-three__content">
					<div class="images-style-three__images">
				  	<?php 
					  	if($image_1){
						 	print '<div class="images-style-three__image-1">';
								if($link){ print '<a href="' . $link . '"' . $target . '>'; }
							  		print '<img src="' . $image_1 .'" alt="' . $title . '" />';
								if($link){ print '</a>'; } 
						 	print '</div>';  
					  	} 
					  	if($image_2){ 
					  		print '<div class="images-style-three__image-2">';
								if($link){ print '<a href="' . $link . '"' . $target . '>'; }
								  print '<img src="' . $image_2 .'" alt="' . $title . '" />';
								if($link){ print '</a>'; }
						 	print '</div>';  
					  	} 
				  	?> 

					</div>
			 	</div>
		  	</div>
        <?php } ?>
		<?php return preg_replace('/ +/'," ", ob_get_clean()) ?>
		<?php            
		} 

	}
endif;   
