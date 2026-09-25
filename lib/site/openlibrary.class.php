<?php
/* 	
    Open Media Collectors Database
    Copyright (C) 2026 Nathaniel Clark

    This program is free software; you can redistribute it and/or
    modify it under the terms of the GNU General Public License
    as published by the Free Software Foundation; either version 2
    of the License, or (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 */
include_once("./lib/SitePlugin.class.php");

function olid2cover($id, $size = "L") {
    $short_id = end(explode("/", $id));

    return "https://covers.openlibrary.org/b/olid/".$short_id."-".$size.".jpg";
}

/* Search page:
 *
 *
 */
class openlibrary extends SitePlugin {
    // An incomplete list of MARC 21 languages
    // http://www.loc.gov/marc/languages/language_name.html
    var $LANGMAP = array( "ara" => "arabic",
                          "chi" => "chinese",
                          "eng" => "english",
                          "fre" => "french",
                          "ger" => "german",
                          "gre" => "greek",
                          "heb" => "hebrew",
                          "ita" => "italian",
                          "jpn" => "japanese",
                          "lat" => "latin",
                          "por" => "portugese",
                          "rus" => "russian",
                          "spa" => "spanish",
    );

	function __construct($site_type) {
		parent::__construct($site_type);
	}

    private $keys = array('key','title','alternate_title','author_name','cover_i','format','subtitle','editions',
                          'format','lcc','isbn','language','publish_year','publisher','number_of_pages_median',
                          'subject_key');

	function queryListing($page_no, $items_per_page, $offset, $s_item_type, $search_vars_r) {
        $queryUrl = "https://openlibrary.org/search.json?" .
            "fields=" . implode(",", $this->keys) .
            "&q=" . urlencode($search_vars_r['search']);

        $pageBuffer = $this->fetchURI($queryUrl);

        if (strlen($pageBuffer) == 0)
            return FALSE;
        
        $data = json_decode($pageBuffer);

        if ($data->{"numFound"} == 0)
            return FALSE;

        foreach($data->{"docs"} as $doc) {
            // if $doc->{"editions"}->{"numFound"} == 1
            $ed = $doc->{'editions'}->{"docs"}[0];

            $title = $ed->{'title'};

			$cover = NULL;
			if (isset($doc->{'cover_i'}))
				$cover = olid2cover($ed->{'key'}, 'S');

            $search_data = array(
                'the_title' => $title,
                'ol_id' => $ed->{'key'},
				'no_pages' => $doc->{'number_of_pages_median'},
				'author_name' => $doc->{'author_name'},
            );

			// @@ languages
			
            // optional values
            foreach(['alternate_title', 'subtitle', 'cover_i'] as $lem)
                if (isset($ed->{$lem}))
                    $search_data[$lem] = $ed->{$lem};
                else if (isset($doc->{$lem}))
                    $search_data[$lem] = $doc->{$lem};

            // optional array values (only take [0])
            foreach(['publish_year', 'publisher', 'format', 'lcc', 'language', 'format'] as $lem)
                if (isset($ed->{$lem}))
                    $search_data[$lem] = $ed->{$lem}[0];
                else if (isset($doc->{$lem}))
                    $search_data[$lem] = $doc->{$lem}[0];

            if (isset($ed->{'isbn'}))
                foreach($ed->{'isbn'} as $isbn)
                    if (strlen($isbn) == 13)
                        $search_data['isbn13'] = $isbn;
                    else if (strlen($isbn) == 10)
                        $search_data['isbn'] = $isbn;
                // else if $doc->{'isbn'}
           
            $this->addListingRow(
                $title,
                $cover,
                NULL,
                $search_data);
        }

        return TRUE;
	}

	function queryItem($search_attributes_r, $s_item_type) {
        //$data = $search_attributes_r['attributes'];
		$data = $search_attributes_r;

		$this->addItemAttribute('title', $data['the_title']);

        if (isset($data['alternate_title']))
            $this->addItemAttribute('alt_title', $data['alternate_title']);
        else if (isset($data['subtitle']))
            $this->addItemAttribute('alt_title', $data['the_title']. ': '.$data['subtitle']);
        else
            $this->addItemAttribute('alt_title', $data['the_title']);

        foreach($data['author_name'] as $author)
            $this->addItemAttribute('author', $author);

		if (isset($data['cover_i']))
			$this->addItemAttribute('imageurl', olid2cover($data['ol_id'], 'L'));
		if (isset($data['publish_year']))
			$this->addItemAttribute('pub_year', $data['publish_year']);
		if (isset($data['lcc']))
			$this->addItemAttribute('loc_class', $this->process_lcc($data['lcc']));
        if (isset($data['format']))
            $this->addItemAttribute('binding', ucfirst(strtolower($data['format'])));
            
        // @@ foreach language -> text_lang

        // @@ subject_key -> genre

        return TRUE;
	}

	function process_lcc($lcc) {
		$arr = explode('.', $lcc);
		if (sizeof($arr) != 3)
			return $lcc;
		$val0 = str_replace('-', '', $arr[0]);
		if ($arr[1] == 0)
			return implode('.', [$val0, $arr[2]]);
		else
			return implode('.', [$val0, intval($arr[1]), $arr[2]]);
	}
}
?>
