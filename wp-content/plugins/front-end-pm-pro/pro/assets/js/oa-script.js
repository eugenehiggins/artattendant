	jQuery(document).ready(function(){		
				var count = fep_oa_script.count;
				jQuery(document).on('click', '.fep_oa_remove', function(){
					jQuery(this).parent().parent().remove();
				});
				
				jQuery('.fep_oa_add').on('click',function(){
					count++;
					jQuery('#fep_oa_add_more_here').append('<div><span><input type="text"  placeholder="'+fep_oa_script.name+'" required name="oa_admins[oa_'+count+'][name]" value=""/></span><span><input type="text"  placeholder="'+fep_oa_script.username+'" required name="oa_admins[oa_'+count+'][username]" value=""/></span><span><input type="button" class="button button-small fep_oa_remove" value="'+fep_oa_script.remove+'" /></span></div>' );
            		return false;			
				});        
			});