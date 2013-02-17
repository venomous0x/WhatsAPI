Feb 14, 2013
============
- Merge pull request #271 from facine/master Fix properties typo. && Add auto-upload images to WhatsApp servers and auto-generate icon.
- Critical: Fix properties typo. Update functions doc comment in WhatsProt class. Add auto-message id to: Location, sendStatusUpdate and RequestLastSeen. And add auto-upload images to WhatsApp servers and auto-generate icon.
- Merge pull request #268 from facine/master.
- Add support to send messages to groups.
- Fix property typo.
- Convert code using Sensio Labs' PHP Coding Standars Fixer.

Feb 13, 2013
============
- Merge pull request #267 from facine/master Add support for new registration and identification method.
- Remove partial registration method file.
- Update WhatsApp version.
- Add check response in request and registration code.
- Add support for new registration method.
- Add support for new identification method.
- Remove unused properties.

Feb 04, 2013
============
- Merge pull request #260 from TheKirk/master Merged, hopefully it'll fully integrated into the lib later.

Feb 01, 2013
============
- Added partial support for Registration v2.

Nov 28, 2012
============
- Merge pull request #166 from wertarbyte/fix_RequestLastSeen add missing msgid parameters to RequestLastSeen.
- Updated README.md.
- Merge branch 'beldar-master'.
- Checkpoint-28-11.
- Merge branch 'master' of https://github.com/beldar/WhatsAPI into beldar-master.

Nov 27, 2012
============
- Lots of changes, new outqueue system, auto msgid, auto send nickname and presence, node processing, and more see testprotocol.php for more details.

Nov 26, 2012
============
- Implemented SendPrecense, WaitforReceipt working for sending several images in sequence, and also notification node in messages to see notification on phone on message received.

Nov 25, 2012
============
- Merge pull request #173 from alexandresaiz/patch-1. Adding Whatsappify as a real case using WhatsAPI.
- Merge pull request #184 from meandrex/master. Added commandline argument to set status.

Nov 20, 2012
============
- Added commandline argument to set the Status. Check default help for usage.

Nov 17, 2012
============
- Adding Whatsappify as a real case using WhatsAPI.

Nov 16, 2012
============
- Merge pull request #169 from dleivag/master. New sendPresence method.
- Update src/php/whatsprot.class.php. Fix error.

Nov 15, 2012
============
- Update src/php/whatsprot.class.php. Fix mistake in sendPresence method.
- Update src/php/whatsprot.class.php. Add method sendPresence. It allows to switch between states online - offline.

Nov 14, 2012
============
- Add missing msgid parameters to RequestLastSeen.

Nov 13, 2012
============
- Merge pull request #77 from arthursc/master. Add nickname function for Iphone client.
- Merge pull request #162 from dannydamianus/patch-1. Update src/php/whatsprot.class.php.
- Update src/php/whatsprot.class.php. Added set status support.

Nov 10, 2012
============
- Merge pull request #157 from beldar/master. Fix for sending images.
- Merge pull request #156 from atans/master. Remove php close tag.
- Fix for sending images.

Nov 09, 2012
============
- Update src/php/whatsprot.class.php. Remove php close tag.
- Update src/php/rc4.php. Remove php close tag.
- Update src/php/protocol.class.php. Remove php close tag.
- Update src/php/func.php. Remove php close tag.
- Update src/php/exception.php. Remove php close tag.
- Update src/php/decode.php. Remove php close tag.

Nov 08, 2012
============
- Merge pull request #152 from zjorzzzey/master. Fixes crash while polling for messages.
- Fixes crash when more than one node is sent to BinTreeNodeReader::nextTree().

Nov 07, 2012
============
- Merge pull request #144 from zstars/patch-1. Fixed extremely minor bug in peekInt8, fixed #141.
- Fixed extremely minor bug in peekInt8. https://github.com/venomous0x/WhatsAPI/issues/141.
- Merge pull request #138 from lowlevel-studios/master. Implemented protocol 1.2, fixes #126 #127 #130 #134 #135 #136.
- Implemented protocol 1.2 (php).

Oct 10, 2012
============
- Merge pull request #105 from arthursc/master. Added Location & Requestlastseen.

Oct 06, 2012
============
- Added RequestLastSeen.
- Update src/php/whatsprot.class.php. Added Location function.

Sep 29, 2012
============
- Update README.md.

Sep 18, 2012
============
- Added sendNickname function. A nickname has to be sent at the beginning of the session. When not set the Iphone client will display the phonenumber instead of the name. The nickname can be changed during the session.
- Added contrib notes.

Sep 16, 2012
============
- Merge pull request #66 from arthursc/master. [#64].
- Update src/php/whatsprot.class.php.
- Update src/php/whatsprot.class.php.
- Updated MD files, got rid of unused file. Added Pong function (to respond on Whatsapp's PING request): http://xmpp.org/extensions/xep-0199.html#c2s server to client ping. Added if statement to handle incoming PING requests in processInboundData.
- Merge pull request #56 from chid/master. Added iOS password method.
- Merge pull request #63 from cdesjardins/master. Added ability to send images.
- Fix file path in test code.
- Adding image send.

Sep 15, 2012
============
- Added iOS password method.

Sep 13, 2012
============
- Merge pull request #53 from cdesjardins/master. Had a problem handling incomplete messages.
- Add message to incomplete message exception.

Sep 12, 2012
============
- Merge pull request #47 from cdesjardins/master. Login for iOS Accounts.
- Remote annoying printf, if you want to see debug info use -d.
- Added dummy function RequestLastSeen.
- Quick fixes/typo-fix.
- Merge pull request #49 from Phhere/master.
- Code for iOS added, Accountinfo fixed.
- Login fixed for Android/iOS.
- Fixed Password check.
- Update tests/whatsapp.php. Added password check.
- Update tests/whatsapp.php. Make it runable.
- Merge branch 'master' of git://github.com/venomous0x/WhatsAPI.
- Fix issue 44, can now send special chars.

Sep 11, 2012
============
- Sources re-arrange.
- Source re-arrange.

Sep 10, 2012
============
- Merge pull request #20 from cdesjardins/master. Merging @cdesjardins's master, one step toward the next cleaner version. Addressing [#17, #20, #34, #26, #40].
- Get rid of the protocol directory in prep for merge.
- Fixed login again.

Sep 09, 2012
============
- Added debug flag.
- Merge branch 'master' of github.com:cdesjardins/WhatsAPI.

Sep 06, 2012
============
- Small updates to the protocol class.

Aug 30, 2012
============
- Merge branch 'master' of git://github.com/venomous0x/WhatsAPI.

Aug 29, 2012
============
- Merge pull request #30 from vkotovv/master. Fixed login issue (#29).
- Fixed login process for php (Issue #29).

Aug 22, 2012
============
- Update php/protocol/protocol.class.php.
- Update php/protocol/protocol.class.php looks better ;) maybe slightly faster.
- Update php/protocol/protocol.class.php use the "==" operator for comparison.
- Update php/protocol/protocol.class.php surrounding if when using foreach is redundant.
- Merge pull request #1 from renner96/patch-1 Update php/protocol/protocol.class.php.
- Update php/protocol/protocol.class.php if not needed... foreach only runs if there are elements.

Aug 21, 2012
============
- Merge branch 'master' of git://github.com/renner96/WhatsAPI with some small changes.
- More updates on the protocol parser.
- Merge branch 'master' of git://github.com/poliva/WhatsAPI.
- Can now login with the protocol parser.

Aug 17, 2012
============
- Remove comment.
- Add basic commands support in interactive mode.
- Change display of received messages (no raw json).
- Add basic command line client in php.

Aug 16, 2012
============
- More work on the protocol processor.
- Small fixes.
- More work on protocol class
- New protocol parser... not yet working!
- Made the socket read a bit smarter.
- Puts annoying tabs in place.
- Puts a more reasonable timeout on the socket reads.

Jul 11, 2012
============
- Fixed small bug in the retry count handling.

Jul 09, 2012
============
- Adds nickname to call
- Fixed warning about $str in decode.php. Issue #17.
- Clean temporal error loggers.
- Fixed nickname change. Issue #5.
- Fixed pull. Issue #1.

Jun 30, 2012
============
- Made the device, version, and port class member variables.

Jun 29, 2012
============
- POST-JSON API

Jun 28, 2012
============
- Adds message rx ack

Jun 25, 2012
============
- Adds code to handle incomplete messages

Jun 21, 2012
============
- Cleanup and fix line 240 (decode.php) is curriently the value "l$dict" but this should be "last". Issue #7.
- Simple indentation, simplification and cleaning. Issue #9.

May 30, 2012
============
- Updated protocol. Issue #4.

May 29, 2012
============
- Re-arranged php source.
- Adds PHP version.
- Initial commit.
