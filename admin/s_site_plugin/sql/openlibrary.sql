#########################################################
# OpenDb 1.5.0.8RC2 Open Library (openlibrary) Site Plugin
#########################################################

#
# Site Plugin.
#

INSERT INTO s_site_plugin ( site_type, classname, title, image, description, external_url, items_per_page, more_info_url ) VALUES ( 'olibrary', 'openlibrary', 'Open Library', 'openlibrary.png', 'Open Library is an open, editable library catalog, building towards a web page for every book ever published.', 'http://openlibrary.org', 10, 'http://openlibrary.org/isbn/{isbn}' );

#
# Site Plugin Input Fields
#

INSERT INTO s_site_plugin_input_field ( site_type, field, order_no, description, prompt, field_type, default_value, refresh_mask ) VALUES ( 'olibrary', 'search', 1, '', 'Search', 'text', '', '{isbn}' );

#
# Site Plugin Links
#

INSERT INTO s_site_plugin_link ( site_type, s_item_type_group, s_item_type, order_no, description, url, title_url ) VALUES ( 'olibrary', '*', 'BOOK', 1, 'More Info', 'http://openlibrary.org/isbn/{isbn}', '' );


#
# Site Plugin System Attribute Type Map
#

#INSERT INTO s_site_plugin_s_attribute_type_map ( site_type, s_item_type_group, s_item_type, variable, s_attribute_type, lookup_attribute_val_restrict_ind ) VALUES ( 'olibrary', '*', '*', 'genre', 'BOOKGENRE', 'Y' );
#INSERT INTO s_site_plugin_s_attribute_type_map ( site_type, s_item_type_group, s_item_type, variable, s_attribute_type, lookup_attribute_val_restrict_ind ) VALUES ( 'olibrary', '*', '*', 'title', 'ALT_TITLE', 'N' );
#INSERT INTO s_site_plugin_s_attribute_type_map ( site_type, s_item_type_group, s_item_type, variable, s_attribute_type, lookup_attribute_val_restrict_ind ) VALUES ( 'olibrary', '*', '*', 'title', 'S_TITLE', 'N' );

####################################################################################################
# Item Type / Attribute Type relationships
####################################################################################################

INSERT INTO s_attribute_type ( s_attribute_type, description, prompt, input_type, display_type, s_field_type, site_type ) VALUES ( 'OL_ID', 'Open Library Identifier', 'Open Library ID', 'text', 'hidden', '', 'olibrary' );
INSERT INTO s_item_attribute_type ( s_item_type, s_attribute_type, order_no, prompt, compulsory_ind ) VALUES ( 'BOOK', 'OL_ID',  0, '', 'N' );
