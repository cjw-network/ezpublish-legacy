<?php
//
// Created on: <17-Apr-2002 11:05:08 amos>
//
// Copyright (C) 1999-2003 eZ systems as. All rights reserved.
//
// This source file is part of the eZ publish (tm) Open Source Content
// Management System.
//
// This file may be distributed and/or modified under the terms of the
// "GNU General Public License" version 2 as published by the Free
// Software Foundation and appearing in the file LICENSE.GPL included in
// the packaging of this file.
//
// Licencees holding valid "eZ publish professional licences" may use this
// file in accordance with the "eZ publish professional licence" Agreement
// provided with the Software.
//
// This file is provided AS IS with NO WARRANTY OF ANY KIND, INCLUDING
// THE WARRANTY OF DESIGN, MERCHANTABILITY AND FITNESS FOR A PARTICULAR
// PURPOSE.
//
// The "eZ publish professional licence" is available at
// http://ez.no/products/licences/professional/. For pricing of this licence
// please contact us via e-mail to licence@ez.no. Further contact
// information is available at http://ez.no/home/contact/.
//
// The "GNU General Public License" (GPL) is available at
// http://www.gnu.org/copyleft/gpl.html.
//
// Contact licence@ez.no if any conditions of this licencing isn't clear to
// you.
//

$Module = array( 'name' => 'eZURL' );

$ViewList = array();
$ViewList['list'] = array(
    'script' => 'list.php',
    'default_navigation_part' => 'ezsetupnavigationpart',
    'single_post_actions' => array( 'SetValid' => 'SetValid',
                                    'SetInvalid' => 'SetInvalid' ),
    'post_action_parameters' => array( 'SetValid' => array( 'URLSelection' => 'URLSelection' ),
                                       'SetInvalid' => array( 'URLSelection' => 'URLSelection' ) ),
    'params' => array( 'ViewMode' ),
    "unordered_params" => array( "offset" => "Offset" ) );
$ViewList['view'] = array(
    'script' => 'view.php',
    'default_navigation_part' => 'ezsetupnavigationpart',
    'single_post_actions' => array( 'EditObject' => 'EditObject' ),
    'params' => array( 'ID' ) );
$ViewList['edit'] = array(
    'script' => 'edit.php',
    'default_navigation_part' => 'ezsetupnavigationpart',
    'single_post_actions' => array( 'Cancel' => 'Cancel',
                                    'Store' => 'Store' ),
    'params' => array( 'ID' ) );
?>
