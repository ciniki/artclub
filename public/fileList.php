<?php
//
// Description
// -----------
//
// Arguments
// ---------
// api_key:
// auth_token:
// business_id:		The ID of the business to get files for.
// type:			The type of participants to get.  Refer to participantAdd for 
//					more information on types.
//
// Returns
// -------
//
function ciniki_artclub_fileList($ciniki) {
	//
	// Find all the required and optional arguments
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
	$rc = ciniki_core_prepareArgs($ciniki, 'no', array(
		'business_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Business'), 
		'type'=>array('required'=>'no', 'blank'=>'no', 'validlist'=>array('1','2'), 'name'=>'Type'), 
		));
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	$args = $rc['args'];
	
    //  
    // Check access to business_id as owner, or sys admin. 
    //  
    ciniki_core_loadMethod($ciniki, 'ciniki', 'artclub', 'private', 'checkAccess');
    $rc = ciniki_artclub_checkAccess($ciniki, $args['business_id'], 'ciniki.artclub.fileList');
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }   

	ciniki_core_loadMethod($ciniki, 'ciniki', 'users', 'private', 'dateFormat');
	$date_format = ciniki_users_dateFormat($ciniki);
	
	//
	// Load the list of members for an artclub
	//
	$strsql = "SELECT ciniki_artclub_files.id, "
		. "ciniki_artclub_files.type, "
		. "ciniki_artclub_files.type AS type_id, "
		. "ciniki_artclub_files.name, "
		. "ciniki_artclub_files.description, "
		. "ciniki_artclub_files.permalink "
		. "FROM ciniki_artclub_files "
		. "WHERE ciniki_artclub_files.business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' ";
	if( isset($args['type']) && $args['type'] != '' ) {
		$strsql .= "AND type = '" . ciniki_core_dbQuote($ciniki, $args['type']) . "' ";
	}
	$strsql .= "ORDER BY type, publish_date DESC, name";
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryTree');
	if( isset($args['type']) && $args['type'] != '' ) {
		$rc = ciniki_core_dbHashQueryTree($ciniki, $strsql, 'ciniki.artclub', array(
			array('container'=>'files', 'fname'=>'id', 'name'=>'file',
				'fields'=>array('id', 'name', 'permalink', 'description')),
			));
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		if( !isset($rc['files']) ) {
			return array('stat'=>'ok', 'files'=>array());
		}
		return array('stat'=>'ok', 'files'=>$rc['files']);
	} 

	//
	// Return the output sorted by types
	//
	$rc = ciniki_core_dbHashQueryTree($ciniki, $strsql, 'ciniki.artclub', array(
		array('container'=>'types', 'fname'=>'type', 'name'=>'type',
			'fields'=>array('id'=>'type_id', 'name'=>'type'),
			'maps'=>array(
				'type'=>array(
					'1'=>'Membership Applications',
					'2'=>'Newsletters',
					),
			)),
		array('container'=>'files', 'fname'=>'id', 'name'=>'file',
			'fields'=>array('id', 'name', 'permalink', 'description')),
		));
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	if( !isset($rc['types']) ) {
		return array('stat'=>'ok', 'types'=>array());
	}
	return array('stat'=>'ok', 'types'=>$rc['types']);
}
?>
