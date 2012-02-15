<?php
$wp_default_secret_key = 'escriu una frase única teva aquí';

/* filters */
add_filter( 'date_i18n', 'months_with_prep' );

/* functions */
function months_with_prep( $date )
{

	$wrong_months = array( "/ de a/", "/ de A/", "/ de o/", "/ de O/" );
	$good_months = array( " d'a", " d'A", " d'o", " d'O");

	/* " de abril| de agost| de octubre..." -> " d'abril| d'agost| d'octubre..." */
	$pattern = "/ de [aAoO]/";
	if ( preg_match( $pattern, $date ) == 1 ) return preg_replace(
$wrong_months, $good_months, $date );

	return $date;
}

?>
