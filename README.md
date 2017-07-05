Hamlet – secure webmail client for EasyCrypt email privacy service
==================================================================
[EasyCrypt](https://easycrypt.co)


INTRODUCTION
------------
Hamlet is a secure webmail client for desktop browsers that is used with EasyCrypt email privacy service. It performs end-to-end PGP encryption and supports EasyCrypt’s Zero Knowledge operation – email content (body and attachments) and the user's private key never leave the user’s computer.
Hamlet is fully compliant with OpenPGP and PGP/MIME. Future versions are planned to support anonymity and metadata protection.
Hamlet is based on and incorporates code from RoundCube and uses EasyCrypt Service Plugin and EasyCrypt Adaptation Plugin for RoundCube.
All the repos on which Hamlet depends are cloned here to support building Hamlet from this repo alone. 

Supported browsers (tested): Chrome, Firefox, Opera, Safari in non-private mode and Tor Browser Bundle on desktop PCs.
Other desktop browsers will probably work but have not been not tested yet. 
Chrome on Android tablets and iPpad works fine in desktop mode except some minor visual issues in portrait orientation.

Hamlet includes other open source classes/libraries from [TinyMCE][tinymce] rich
text editor, [Firebase][php-jwt] JWT implementation for PHP, [Guzzle][guzzle] PHP HTTP client,
[PHP FIG][http-message].

INSTALLATION
------------
For detailed instructions for installation of RoundCube webmail on your server please refer to the INSTALL document that is located in the same directory
as this README.


LICENSE
-------
This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License (**with exceptions
for skins & plugins**) as published by the Free Software Foundation,
either version 3 of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program. If not, see [www.gnu.org/licenses/][gpl].

This file forms part of the RoundRube Webmail Software for which the
following exception is added: Plugins and Skins which merely make
function calls to the RoundCube Webmail Software, and for that purpose
include it by reference, shall not be considered modifications of
the software.

If you wish to use this file in another project or create a modified
version that will not be part of the RoundCube Webmail Software, you
may remove the exception above and use this source code under the
original version of the license.

CONTACT
-------
For bug reports or feature requests please refer to the tracking system
at [Github][githubissues] or email us at support(at)easycrypt(dot)co
