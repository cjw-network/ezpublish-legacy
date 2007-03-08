<?php
//
// Created on: <22-Apr-2002 15:41:30 bf>
//
// ## BEGIN COPYRIGHT, LICENSE AND WARRANTY NOTICE ##
// SOFTWARE NAME: eZ publish
// SOFTWARE RELEASE: 3.10.x
// COPYRIGHT NOTICE: Copyright (C) 1999-2006 eZ systems AS
// SOFTWARE LICENSE: GNU General Public License v2.0
// NOTICE: >
//   This program is free software; you can redistribute it and/or
//   modify it under the terms of version 2.0  of the GNU General
//   Public License as published by the Free Software Foundation.
//
//   This program is distributed in the hope that it will be useful,
//   but WITHOUT ANY WARRANTY; without even the implied warranty of
//   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//   GNU General Public License for more details.
//
//   You should have received a copy of version 2.0 of the GNU General
//   Public License along with this program; if not, write to the Free
//   Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
//   MA 02110-1301, USA.
//
//
// ## END COPYRIGHT, LICENSE AND WARRANTY NOTICE ##
//

include_once( 'kernel/classes/ezcontentclass.php' );
include_once( 'kernel/classes/ezcontentclassattribute.php' );

include_once( 'kernel/classes/ezcontentobject.php' );
include_once( 'kernel/classes/ezcontentobjectversion.php' );
include_once( 'kernel/classes/ezcontentobjectattribute.php' );

include_once( 'kernel/common/template.php' );
include_once( "lib/ezutils/classes/ezini.php" );
include_once( "lib/ezdb/classes/ezdb.php" );

include_once( 'lib/ezutils/classes/ezdebug.php' );

$tpl =& templateInit();
$http =& eZHTTPTool::instance();

$ObjectID = $Params['ObjectID'];
$EditVersion = $Params['EditVersion'];

$Offset = $Params['Offset'];
$viewParameters = array( 'offset' => $Offset );

if ( $http->hasPostVariable( 'BackButton' )  )
{
    $userRedirectURI = '';
    if ( $http->hasPostVariable( 'RedirectURI' ) )
    {
        $redurectURI = $http->postVariable( 'RedirectURI' );
        $http->removeSessionVariable( 'LastAccessesVersionURI' );
        return $Module->redirectTo( $redurectURI );
    }
    if ( $http->hasSessionVariable( "LastAccessesURI" ) )
        $userRedirectURI = $http->sessionVariable( "LastAccessesURI" );
    return $Module->redirectTo( $userRedirectURI );
}

$object =& eZContentObject::fetch( $ObjectID );
$editWarning = false;

$canEdit = false;
$canRemove = false;

if ( $object === null )
    return $Module->handleError( EZ_ERROR_KERNEL_NOT_AVAILABLE, 'kernel' );

if ( !$object->attribute( 'can_read' ) )
    return $Module->handleError( EZ_ERROR_KERNEL_ACCESS_DENIED, 'kernel' );

if ( $object->attribute( 'can_edit' ) )
    $canEdit = true;

$canRemove = true;

$http =& eZHTTPTool::instance();

if ( $http->hasSessionVariable( 'ExcessVersionHistoryLimit' ) )
{
    $excessLimit = $http->sessionVariable( 'ExcessVersionHistoryLimit' );
    if ( $excessLimit )
        $editWarning = 3;
    $http->removeSessionVariable( 'ExcessVersionHistoryLimit' );
}

if ( $http->hasPostVariable( 'RemoveButton' )  )
{
    if ( !$canEdit )
        return $Module->handleError( EZ_ERROR_KERNEL_ACCESS_DENIED, 'kernel' );
    if ( $http->hasPostVariable( 'DeleteIDArray' ) )
    {
        $db =& eZDB::instance();
        $db->begin();

        $deleteIDArray = $http->postVariable( 'DeleteIDArray' );
        $versionArray = array();
        foreach ( $deleteIDArray as $deleteID )
        {
            $version = eZContentObjectVersion::fetch( $deleteID );
            $versionArray[] = $version->attribute( 'version' );
            if ( $version != null )
            {
                if ( $version->attribute( 'can_remove' ) )
                {
                    $version->remove();
                }
            }
        }
        $db->commit();
    }
}

$user =& eZUser::currentUser();

if ( $Module->isCurrentAction( 'Edit' )  )
{
    if ( !$canEdit )
        return $Module->handleError( EZ_ERROR_KERNEL_ACCESS_DENIED, 'kernel' );

    $versionID = false;

    if ( is_array( $Module->actionParameter( 'VersionKeyArray' ) ) )
    {
        $versionID = array_keys( $Module->actionParameter( 'VersionKeyArray' ) );
        $versionID = $versionID[0];
    }
    else if ( $Module->hasActionParameter( 'VersionID' ) )
        $versionID = $Module->actionParameter( 'VersionID' );
    $version =& $object->version( $versionID );
    if ( !$version )
        $versionID = false;

    if ( $versionID !== false and
         !in_array( $version->attribute( 'status' ), array( EZ_VERSION_STATUS_DRAFT, EZ_VERSION_STATUS_INTERNAL_DRAFT ) ) )
    {
        $editWarning = 1;
        $EditVersion = $versionID;
    }
    else if ( $versionID !== false and
              $version->attribute( 'creator_id' ) != $user->attribute( 'contentobject_id' ) )
    {
        $editWarning = 2;
        $EditVersion = $versionID;
    }
    else
    {
        return $Module->redirectToView( 'edit', array( $ObjectID, $versionID, $version->initialLanguageCode() ) );
    }
}

if ( $Module->isCurrentAction( 'CopyVersion' )  )
{
    if ( !$canEdit )
    {
        return $Module->handleError( EZ_ERROR_KERNEL_ACCESS_DENIED, 'kernel' );
    }

    if ( is_array( $Module->actionParameter( 'VersionKeyArray' ) ) )
    {
        $versionID = array_keys( $Module->actionParameter( 'VersionKeyArray' ) );
        $versionID = $versionID[0];
    }
    else
    {
        $versionID = $Module->actionParameter( 'VersionID' );
    }

    $version =& $object->version( $versionID );
    if ( !$version )
        $versionID = false;

    // if we cannot fetch version with given versionID or if fetched version is
    // an internal-draft then just skip copying and redirect back to the history view
    if ( !$versionID or $version->attribute( 'status' ) == EZ_VERSION_STATUS_INTERNAL_DRAFT )
    {
        $currentVersion = $object->attribute( 'current_version' );
        $Module->redirectToView( 'versions', array( $ObjectID, $currentVersion ) );
        return EZ_MODULE_HOOK_STATUS_CANCEL_RUN;
    }

    $languages = $Module->actionParameter( 'LanguageArray' );
    if ( $languages && array_key_exists( $versionID, $languages ) )
    {
        $language = $languages[$versionID];
    }
    else
    {
        $language = $version->initialLanguageCode();
    }

    if ( !$object->checkAccess( 'edit', false, false, false, $language ) )
    {
        return $Module->handleError( EZ_ERROR_KERNEL_ACCESS_DENIED, 'kernel' );
    }

    $contentINI =& eZINI::instance( 'content.ini' );
    $versionlimit = $contentINI->variable( 'VersionManagement', 'DefaultVersionHistoryLimit' );

    $limitList = $contentINI->variable( 'VersionManagement', 'VersionHistoryClass' );

    $classID = $object->attribute( 'contentclass_id' );
    foreach ( array_keys ( $limitList ) as $key )
    {
        if ( $classID == $key )
            $versionlimit =& $limitList[$key];
    }
    if ( $versionlimit < 2 )
        $versionlimit = 2;

    $versionCount = $object->getVersionCount();
    if ( $versionCount < $versionlimit )
    {
        $db =& eZDB::instance();
        $db->begin();
        $newVersionID = $object->copyRevertTo( $versionID, $language );
        $db->commit();

        if ( !$http->hasPostVariable( 'DoNotEditAfterCopy' ) )
        {
            return $Module->redirectToView( 'edit', array( $ObjectID, $newVersionID, $language ) );
        }
    }
    else
    {
        $params = array( 'conditions'=> array( 'status' => 3 ) );
        $versions =& $object->versions( true, $params );
        if ( count( $versions ) > 0 )
        {
            $modified = $versions[0]->attribute( 'modified' );
            $removeVersion =& $versions[0];
            foreach ( array_keys( $versions ) as $versionKey )
            {
                $version =& $versions[$versionKey];
                $currentModified = $version->attribute( 'modified' );
                if ( $currentModified < $modified )
                {
                    $modified = $currentModified;
                    $removeVersion = $version;
                }
            }

            $db =& eZDB::instance();
            $db->begin();
            $removeVersion->remove();
            $newVersionID = $object->copyRevertTo( $versionID, $language );
            $db->commit();

            if ( !$http->hasPostVariable( 'DoNotEditAfterCopy' ) )
            {
                return $Module->redirectToView( 'edit', array( $ObjectID, $newVersionID, $language ) );
            }
        }
        else
        {
            $http->setSessionVariable( 'ExcessVersionHistoryLimit', true );
            $currentVersion = $object->attribute( 'current_version' );
            $Module->redirectToView( 'versions', array( $ObjectID, $currentVersion ) );
            return EZ_MODULE_HOOK_STATUS_CANCEL_RUN;
        }
    }
}

$res =& eZTemplateDesignResource::instance();
$res->setKeys( array( array( 'object', $object->attribute( 'id' ) ), // Object ID
                      array( 'class', $object->attribute( 'contentclass_id' ) ), // Class ID
                      array( 'class_identifier', $object->attribute( 'class_identifier' ) ), // Class identifier
                      array( 'section_id', $object->attribute( 'section_id' ) ) // Section ID
                      ) ); // Section ID, 0 so far

include_once( 'kernel/classes/ezsection.php' );
eZSection::setGlobalID( $object->attribute( 'section_id' ) );
$versionArray =( isset( $versionArray ) and is_array( $versionArray ) ) ? array_unique( $versionArray ) : array();
$LastAccessesVersionURI = $http->hasSessionVariable( 'LastAccessesVersionURI' ) ? $http->sessionVariable( 'LastAccessesVersionURI' ) : null;
$explodedURI = $LastAccessesVersionURI ? explode ( '/', $LastAccessesVersionURI ) : null;
if ( $LastAccessesVersionURI and is_array( $versionArray ) and !in_array( $explodedURI[3], $versionArray ) )
    $tpl->setVariable( 'redirect_uri', $http->sessionVariable( 'LastAccessesVersionURI' ) );

$versions =& $object->versions();

$tpl->setVariable( 'view_parameters', $viewParameters );
$tpl->setVariable( 'object', $object );
$tpl->setVariable( 'edit_version', $EditVersion );
$tpl->setVariable( 'versions', $versions );
$tpl->setVariable( 'edit_warning', $editWarning );
$tpl->setVariable( 'can_edit', $canEdit );
//$tpl->setVariable( 'can_remove', $canRemove );
$tpl->setVariable( 'user_id', $user->attribute( 'contentobject_id' ) );

eZDebug::writeNotice( 'The versions view has been deprecated, please use the /content/history/ view instead' );

$Result = array();
$Result['content'] =& $tpl->fetch( 'design:content/versions.tpl' );
$Result['path'] = array( array( 'text' => ezi18n( 'kernel/content', 'Versions' ),
                                'url' => false ) );

?>
