
publish gpo ready

publishGPOReady

​ Unhandled Promise Rejection: NotAllowedError: The request is not allowed by the user agent or the platform in the current context, possibly because the user denied permission.

r203249 ko
r202372 ko but tries to load
r202371 ko
r202351 ko
r202350 ko
r145113 ok

202371 ok
202372ok
r203249ok

WHERE global_id='12'




$ svn update /var/tournoi/games/newyorkzoo 2>&1


Updating '/var/tournoi/games/newyorkzoo':
At revision 203992.

$ svn log /var/tournoi/games/newyorkzoo 2>&1


------------------------------------------------------------------------
r203988 | bgastudio | 2023-08-28 15:17:07 +0200 (Mon, 28 Aug 2023) | 6 lines

<mizutismask, newyorkzoo> fix(96902): kangaroo and flamingo breedings are triggered when starting a new circle over the board
fix(96762): kangaroo breed was not triggered when starting another turn of the board (25->4)
style(target): makes it white on fences (breeding) and more visible on possible squares for placing fences
fix: prevents triggering actions clicking on fences or attractions when not active
feat: rephrasing breeding state action phrase
fix(96762): flamingo breed was not triggered when starting another turn of the board

r203410 | bgastudio | 2023-08-23 15:06:05 +0200 (Wed, 23 Aug 2023) | 5 lines

<mizutismask, newyorkzoo> feat: ask confirmation if you don’t select all the breedings you could have
feat: add new highlight layer on top of everything to prevent double highlight on square and anml_square, only the top layer highlights
style(controls): makes mirror and rotate buttons more visible
style(fenceActionZone): add shadows to see the depth better

------------------------------------------------------------------------
r203333 | bgastudio | 2023-08-23 09:01:41 +0200 (Wed, 23 Aug 2023) | 2 lines

r203279 | bgastudio | 2023-08-22 17:41:54 +0200 (Tue, 22 Aug 2023) | 2 lines

<mizutismask, newyorkzoo> style: allows target on every type of square but only when placing a fence
feat: on take animals action, offers to give up on the 2 animals to take 1 other type
------------------------------------------------------------------------
r203251 | bgastudio | 2023-08-22 11:14:34 +0200 (Tue, 22 Aug 2023) | 3 lines

<mizutismask, newyorkzoo> fix(96302): Highlighting not properly updated when rotating or mirroring an enclosure
feat: makes clearer what to do when clicking take animals on player turn without selecting an animal zone
feat: rename fence to enclosure
------------------------------------------------------------------------
r203249 | bgastudio | 2023-08-22 10:04:17 +0200 (Tue, 22 Aug 2023) | 7 lines

<mizutismask, newyorkzoo> feat: prevents target on hover filler patches
fix: listens for clicks only on player houses
feat(keepFromFullFence): just asks yes or no, don’t let the user choose a house or anything else
feat: makes clearer what to do to populate a new fence, no confirm needed
feat: makes debug setup stuff unavailable in production
fix: resets the elephant at the right place
style: restyle player panel
------------------------------------------------------------------------
r203200 | bgastudio | 2023-08-22 09:01:31 +0200 (Tue, 22 Aug 2023) | 2 lines

r202420 | bgastudio | 2023-08-17 09:01:56 +0200 (Thu, 17 Aug 2023) | 2 lines

r202372 | bgastudio | 2023-08-16 17:54:51 +0200 (Wed, 16 Aug 2023) | 5 lines

<mizutismask, newyorkzoo> * a511c9e - (HEAD -> develop) refacto: disable js logs and comment php dumps (71 minutes ago) <mizutismask>
* 43a90b7 - (origin/develop) feat: add progression (2 hours ago) <mizutismask>
* 8c0a374 - fix: add title to animals in the logs (3 hours ago) <mizutismask>
* b6c804b - fix: spectator mode error (6 hours ago) <mizutismask>
* 3d5d33c - fix(staticAnalysis): adds missing check action and renames patch to fence (6 hours ago) <mizutismask>
------------------------------------------------------------------------
r202371 | bgastudio | 2023-08-16 17:54:48 +0200 (Wed, 16 Aug 2023) | 5 lines

<mizutismask, newyorkzoo> * a511c9e - (HEAD -> develop) refacto: disable js logs and comment php dumps (71 minutes ago) <mizutismask>
* 43a90b7 - (origin/develop) feat: add progression (2 hours ago) <mizutismask>
* 8c0a374 - fix: add title to animals in the logs (3 hours ago) <mizutismask>
* b6c804b - fix: spectator mode error (6 hours ago) <mizutismask>
* 3d5d33c - fix(staticAnalysis): adds missing check action and renames patch to fence (6 hours ago) <mizutismask>
------------------------------------------------------------------------
r202370 | bgastudio | 2023-08-16 17:54:44 +0200 (Wed, 16 Aug 2023) | 5 lines

<mizutismask, newyorkzoo> * a511c9e - (HEAD -> develop) refacto: disable js logs and comment php dumps (71 minutes ago) <mizutismask>
* 43a90b7 - (origin/develop) feat: add progression (2 hours ago) <mizutismask>
* 8c0a374 - fix: add title to animals in the logs (3 hours ago) <mizutismask>
* b6c804b - fix: spectator mode error (6 hours ago) <mizutismask>
* 3d5d33c - fix(staticAnalysis): adds missing check action and renames patch to fence (6 hours ago) <mizutismask>
------------------------------------------------------------------------
r202351 | bgastudio | 2023-08-16 11:38:35 +0200 (Wed, 16 Aug 2023) | 1 line

<mizutismask, newyorkzoo> private alpha ready
------------------------------------------------------------------------
r202350 | bgastudio | 2023-08-16 11:38:33 +0200 (Wed, 16 Aug 2023) | 1 line

<mizutismask, newyorkzoo> private alpha ready
------------------------------------------------------------------------
r202287 | bgastudio | 2023-08-16 09:02:44 +0200 (Wed, 16 Aug 2023) | 2 lines

r202286 | bgastudio | 2023-08-16 09:02:42 +0200 (Wed, 16 Aug 2023) | 2 lines

r202131 | bgastudio | 2023-08-15 09:01:59 +0200 (Tue, 15 Aug 2023) | 2 lines

r202130 | bgastudio | 2023-08-15 09:01:56 +0200 (Tue, 15 Aug 2023) | 2 lines

r201684 | bgastudio | 2023-08-12 09:02:01 +0200 (Sat, 12 Aug 2023) | 2 lines

r201498 | bgastudio | 2023-08-11 09:02:03 +0200 (Fri, 11 Aug 2023) | 2 lines

r201497 | bgastudio | 2023-08-11 09:02:01 +0200 (Fri, 11 Aug 2023) | 2 lines

r201306 | bgastudio | 2023-08-10 09:02:30 +0200 (Thu, 10 Aug 2023) | 2 lines

r201114 | bgastudio | 2023-08-09 09:02:45 +0200 (Wed, 09 Aug 2023) | 2 lines

r201113 | bgastudio | 2023-08-09 09:02:43 +0200 (Wed, 09 Aug 2023) | 2 lines

r200966 | bgastudio | 2023-08-08 09:02:22 +0200 (Tue, 08 Aug 2023) | 2 lines

r200965 | bgastudio | 2023-08-08 09:02:20 +0200 (Tue, 08 Aug 2023) | 2 lines

r200399 | bgastudio | 2023-08-05 09:02:09 +0200 (Sat, 05 Aug 2023) | 2 lines

r200252 | bgastudio | 2023-08-04 09:01:57 +0200 (Fri, 04 Aug 2023) | 2 lines

r195906 | bgastudio | 2023-07-06 09:02:02 +0200 (Thu, 06 Jul 2023) | 2 lines

r195776 | bgastudio | 2023-07-05 09:02:29 +0200 (Wed, 05 Jul 2023) | 2 lines

r195775 | bgastudio | 2023-07-05 09:02:27 +0200 (Wed, 05 Jul 2023) | 2 lines

r195333 | bgastudio | 2023-07-01 09:01:54 +0200 (Sat, 01 Jul 2023) | 2 lines

r195207 | bgastudio | 2023-06-30 09:01:56 +0200 (Fri, 30 Jun 2023) | 2 lines

r195025 | bgastudio | 2023-06-29 09:02:14 +0200 (Thu, 29 Jun 2023) | 2 lines

r194739 | bgastudio | 2023-06-27 09:02:30 +0200 (Tue, 27 Jun 2023) | 2 lines

r194360 | bgastudio | 2023-06-24 09:03:17 +0200 (Sat, 24 Jun 2023) | 2 lines

r194191 | bgastudio | 2023-06-23 09:02:38 +0200 (Fri, 23 Jun 2023) | 2 lines

r194190 | bgastudio | 2023-06-23 09:02:36 +0200 (Fri, 23 Jun 2023) | 2 lines

r194018 | bgastudio | 2023-06-22 09:02:31 +0200 (Thu, 22 Jun 2023) | 2 lines

r194017 | bgastudio | 2023-06-22 09:02:29 +0200 (Thu, 22 Jun 2023) | 2 lines

r193830 | bgastudio | 2023-06-21 09:02:53 +0200 (Wed, 21 Jun 2023) | 2 lines

r193618 | bgastudio | 2023-06-20 09:02:31 +0200 (Tue, 20 Jun 2023) | 2 lines

r193617 | bgastudio | 2023-06-20 09:02:28 +0200 (Tue, 20 Jun 2023) | 2 lines

r193213 | bgastudio | 2023-06-17 09:02:37 +0200 (Sat, 17 Jun 2023) | 2 lines

r193212 | bgastudio | 2023-06-17 09:02:34 +0200 (Sat, 17 Jun 2023) | 2 lines

r193074 | bgastudio | 2023-06-16 09:02:27 +0200 (Fri, 16 Jun 2023) | 2 lines

r193073 | bgastudio | 2023-06-16 09:02:24 +0200 (Fri, 16 Jun 2023) | 2 lines

r192905 | bgastudio | 2023-06-15 09:02:16 +0200 (Thu, 15 Jun 2023) | 2 lines

r192904 | bgastudio | 2023-06-15 09:02:14 +0200 (Thu, 15 Jun 2023) | 2 lines

r192773 | bgastudio | 2023-06-14 09:07:08 +0200 (Wed, 14 Jun 2023) | 2 lines

r192762 | bgastudio | 2023-06-14 09:02:22 +0200 (Wed, 14 Jun 2023) | 2 lines

r192761 | bgastudio | 2023-06-14 09:02:19 +0200 (Wed, 14 Jun 2023) | 2 lines

r192597 | bgastudio | 2023-06-13 09:02:43 +0200 (Tue, 13 Jun 2023) | 2 lines

r192596 | bgastudio | 2023-06-13 09:02:41 +0200 (Tue, 13 Jun 2023) | 2 lines

r191978 | bgastudio | 2023-06-08 09:02:30 +0200 (Thu, 08 Jun 2023) | 2 lines

r191927 | bgastudio | 2023-06-07 09:15:34 +0200 (Wed, 07 Jun 2023) | 2 lines

r191876 | bgastudio | 2023-06-07 09:02:47 +0200 (Wed, 07 Jun 2023) | 2 lines

r191875 | bgastudio | 2023-06-07 09:02:45 +0200 (Wed, 07 Jun 2023) | 2 lines

r191753 | bgastudio | 2023-06-06 09:02:08 +0200 (Tue, 06 Jun 2023) | 2 lines

r191752 | bgastudio | 2023-06-06 09:02:06 +0200 (Tue, 06 Jun 2023) | 2 lines

r191466 | bgastudio | 2023-06-03 09:02:52 +0200 (Sat, 03 Jun 2023) | 2 lines

r181976 | bgastudio | 2023-03-22 09:02:21 +0100 (Wed, 22 Mar 2023) | 2 lines

r181446 | bgastudio | 2023-03-18 09:02:56 +0100 (Sat, 18 Mar 2023) | 2 lines

r181380 | bgastudio | 2023-03-17 09:37:37 +0100 (Fri, 17 Mar 2023) | 2 lines

r181374 | bgastudio | 2023-03-17 09:26:24 +0100 (Fri, 17 Mar 2023) | 2 lines

r181368 | bgastudio | 2023-03-17 09:18:05 +0100 (Fri, 17 Mar 2023) | 2 lines

r181322 | bgastudio | 2023-03-17 09:04:05 +0100 (Fri, 17 Mar 2023) | 2 lines

r181066 | bgastudio | 2023-03-15 09:03:47 +0100 (Wed, 15 Mar 2023) | 2 lines

r181065 | bgastudio | 2023-03-15 09:03:44 +0100 (Wed, 15 Mar 2023) | 2 lines

r180919 | bgastudio | 2023-03-14 09:02:43 +0100 (Tue, 14 Mar 2023) | 2 lines

r180696 | bgastudio | 2023-03-12 09:01:59 +0100 (Sun, 12 Mar 2023) | 2 lines

r180604 | bgastudio | 2023-03-11 09:03:05 +0100 (Sat, 11 Mar 2023) | 2 lines

r180474 | bgastudio | 2023-03-10 09:03:49 +0100 (Fri, 10 Mar 2023) | 2 lines

r180473 | bgastudio | 2023-03-10 09:03:46 +0100 (Fri, 10 Mar 2023) | 2 lines

r180403 | bgastudio | 2023-03-09 10:13:44 +0100 (Thu, 09 Mar 2023) | 2 lines

r180402 | bgastudio | 2023-03-09 10:09:37 +0100 (Thu, 09 Mar 2023) | 2 lines

r180400 | bgastudio | 2023-03-09 09:59:49 +0100 (Thu, 09 Mar 2023) | 2 lines

r180393 | bgastudio | 2023-03-09 09:46:15 +0100 (Thu, 09 Mar 2023) | 2 lines

r180352 | bgastudio | 2023-03-09 09:06:52 +0100 (Thu, 09 Mar 2023) | 2 lines

r180351 | bgastudio | 2023-03-09 09:06:49 +0100 (Thu, 09 Mar 2023) | 2 lines

r180195 | bgastudio | 2023-03-08 09:03:10 +0100 (Wed, 08 Mar 2023) | 2 lines

r180044 | bgastudio | 2023-03-07 09:02:18 +0100 (Tue, 07 Mar 2023) | 2 lines

r179734 | bgastudio | 2023-03-04 09:03:04 +0100 (Sat, 04 Mar 2023) | 2 lines

r179733 | bgastudio | 2023-03-04 09:03:02 +0100 (Sat, 04 Mar 2023) | 2 lines

r179613 | bgastudio | 2023-03-03 09:03:39 +0100 (Fri, 03 Mar 2023) | 2 lines

r179612 | bgastudio | 2023-03-03 09:03:36 +0100 (Fri, 03 Mar 2023) | 2 lines

r179529 | bgastudio | 2023-03-02 09:03:33 +0100 (Thu, 02 Mar 2023) | 2 lines

r178073 | bgastudio | 2023-02-17 09:03:21 +0100 (Fri, 17 Feb 2023) | 2 lines

r177984 | bgastudio | 2023-02-16 10:20:41 +0100 (Thu, 16 Feb 2023) | 2 lines

r177983 | bgastudio | 2023-02-16 10:16:00 +0100 (Thu, 16 Feb 2023) | 2 lines

r177982 | bgastudio | 2023-02-16 10:11:11 +0100 (Thu, 16 Feb 2023) | 2 lines

r177981 | bgastudio | 2023-02-16 09:58:34 +0100 (Thu, 16 Feb 2023) | 2 lines

r177980 | bgastudio | 2023-02-16 09:53:58 +0100 (Thu, 16 Feb 2023) | 2 lines

r177979 | bgastudio | 2023-02-16 09:49:20 +0100 (Thu, 16 Feb 2023) | 2 lines

r177977 | bgastudio | 2023-02-16 09:35:09 +0100 (Thu, 16 Feb 2023) | 2 lines

r177967 | bgastudio | 2023-02-16 09:03:07 +0100 (Thu, 16 Feb 2023) | 2 lines

r177966 | bgastudio | 2023-02-16 09:03:05 +0100 (Thu, 16 Feb 2023) | 2 lines

r177910 | bgastudio | 2023-02-15 10:25:19 +0100 (Wed, 15 Feb 2023) | 2 lines

r177909 | bgastudio | 2023-02-15 10:20:48 +0100 (Wed, 15 Feb 2023) | 2 lines

r177906 | bgastudio | 2023-02-15 10:16:13 +0100 (Wed, 15 Feb 2023) | 2 lines

r177578 | bgastudio | 2023-02-11 09:05:57 +0100 (Sat, 11 Feb 2023) | 2 lines

r177577 | bgastudio | 2023-02-11 09:05:55 +0100 (Sat, 11 Feb 2023) | 2 lines

r177221 | bgastudio | 2023-02-08 09:04:22 +0100 (Wed, 08 Feb 2023) | 2 lines

r177220 | bgastudio | 2023-02-08 09:04:19 +0100 (Wed, 08 Feb 2023) | 2 lines

r177071 | bgastudio | 2023-02-07 09:03:57 +0100 (Tue, 07 Feb 2023) | 2 lines

r177070 | bgastudio | 2023-02-07 09:03:55 +0100 (Tue, 07 Feb 2023) | 2 lines

r176776 | bgastudio | 2023-02-04 09:04:10 +0100 (Sat, 04 Feb 2023) | 2 lines

r176775 | bgastudio | 2023-02-04 09:04:08 +0100 (Sat, 04 Feb 2023) | 2 lines

r155194 | bgastudio | 2022-08-22 09:12:50 +0200 (Mon, 22 Aug 2022) | 2 lines

r154707 | bgastudio | 2022-08-18 09:10:37 +0200 (Thu, 18 Aug 2022) | 2 lines

r154550 | bgastudio | 2022-08-17 09:09:10 +0200 (Wed, 17 Aug 2022) | 2 lines

r154390 | bgastudio | 2022-08-16 09:07:32 +0200 (Tue, 16 Aug 2022) | 2 lines

r154056 | bgastudio | 2022-08-13 09:17:34 +0200 (Sat, 13 Aug 2022) | 2 lines

r153955 | bgastudio | 2022-08-12 09:07:48 +0200 (Fri, 12 Aug 2022) | 2 lines

r153857 | bgastudio | 2022-08-11 09:07:16 +0200 (Thu, 11 Aug 2022) | 2 lines

r153856 | bgastudio | 2022-08-11 09:07:13 +0200 (Thu, 11 Aug 2022) | 2 lines

r153736 | bgastudio | 2022-08-10 09:20:27 +0200 (Wed, 10 Aug 2022) | 2 lines

r153716 | bgastudio | 2022-08-10 09:07:05 +0200 (Wed, 10 Aug 2022) | 2 lines

r153571 | bgastudio | 2022-08-09 09:06:51 +0200 (Tue, 09 Aug 2022) | 2 lines

r153570 | bgastudio | 2022-08-09 09:06:49 +0200 (Tue, 09 Aug 2022) | 2 lines

r153458 | bgastudio | 2022-08-08 09:07:00 +0200 (Mon, 08 Aug 2022) | 2 lines

r153226 | bgastudio | 2022-08-06 09:06:28 +0200 (Sat, 06 Aug 2022) | 2 lines

r153065 | bgastudio | 2022-08-05 09:06:29 +0200 (Fri, 05 Aug 2022) | 2 lines

r153064 | bgastudio | 2022-08-05 09:06:25 +0200 (Fri, 05 Aug 2022) | 2 lines

r152958 | bgastudio | 2022-08-04 09:18:44 +0200 (Thu, 04 Aug 2022) | 2 lines

r152938 | bgastudio | 2022-08-04 09:07:33 +0200 (Thu, 04 Aug 2022) | 2 lines

r152937 | bgastudio | 2022-08-04 09:07:30 +0200 (Thu, 04 Aug 2022) | 2 lines

r152818 | bgastudio | 2022-08-03 09:07:06 +0200 (Wed, 03 Aug 2022) | 2 lines

r152817 | bgastudio | 2022-08-03 09:07:04 +0200 (Wed, 03 Aug 2022) | 2 lines

r152395 | bgastudio | 2022-07-31 09:05:31 +0200 (Sun, 31 Jul 2022) | 2 lines

r152394 | bgastudio | 2022-07-31 09:05:29 +0200 (Sun, 31 Jul 2022) | 2 lines

r152296 | bgastudio | 2022-07-30 09:07:30 +0200 (Sat, 30 Jul 2022) | 2 lines

r152169 | bgastudio | 2022-07-29 09:09:29 +0200 (Fri, 29 Jul 2022) | 2 lines

r152041 | bgastudio | 2022-07-28 09:11:27 +0200 (Thu, 28 Jul 2022) | 2 lines

r151350 | bgastudio | 2022-07-23 09:06:04 +0200 (Sat, 23 Jul 2022) | 2 lines

r151349 | bgastudio | 2022-07-23 09:06:02 +0200 (Sat, 23 Jul 2022) | 2 lines

r151230 | bgastudio | 2022-07-22 09:08:53 +0200 (Fri, 22 Jul 2022) | 2 lines

r151229 | bgastudio | 2022-07-22 09:08:50 +0200 (Fri, 22 Jul 2022) | 2 lines

r151115 | bgastudio | 2022-07-21 09:05:52 +0200 (Thu, 21 Jul 2022) | 2 lines

r151001 | bgastudio | 2022-07-20 09:05:42 +0200 (Wed, 20 Jul 2022) | 2 lines

r151000 | bgastudio | 2022-07-20 09:05:40 +0200 (Wed, 20 Jul 2022) | 2 lines

r150605 | bgastudio | 2022-07-16 09:05:21 +0200 (Sat, 16 Jul 2022) | 2 lines

r150604 | bgastudio | 2022-07-16 09:05:19 +0200 (Sat, 16 Jul 2022) | 2 lines

r150371 | bgastudio | 2022-07-14 09:06:30 +0200 (Thu, 14 Jul 2022) | 2 lines

r150370 | bgastudio | 2022-07-14 09:06:28 +0200 (Thu, 14 Jul 2022) | 2 lines

r150369 | bgastudio | 2022-07-14 09:06:25 +0200 (Thu, 14 Jul 2022) | 2 lines

r150183 | bgastudio | 2022-07-13 09:06:05 +0200 (Wed, 13 Jul 2022) | 2 lines

r150182 | bgastudio | 2022-07-13 09:06:02 +0200 (Wed, 13 Jul 2022) | 2 lines

r150181 | bgastudio | 2022-07-13 09:05:59 +0200 (Wed, 13 Jul 2022) | 2 lines

r150004 | bgastudio | 2022-07-12 09:05:21 +0200 (Tue, 12 Jul 2022) | 2 lines

r150003 | bgastudio | 2022-07-12 09:05:18 +0200 (Tue, 12 Jul 2022) | 2 lines

r150002 | bgastudio | 2022-07-12 09:05:14 +0200 (Tue, 12 Jul 2022) | 2 lines

r149873 | bgastudio | 2022-07-11 09:04:55 +0200 (Mon, 11 Jul 2022) | 2 lines

r149872 | bgastudio | 2022-07-11 09:04:53 +0200 (Mon, 11 Jul 2022) | 2 lines

r149793 | bgastudio | 2022-07-10 09:04:32 +0200 (Sun, 10 Jul 2022) | 2 lines

r149792 | bgastudio | 2022-07-10 09:04:28 +0200 (Sun, 10 Jul 2022) | 2 lines

r148088 | bgastudio | 2022-06-27 09:04:02 +0200 (Mon, 27 Jun 2022) | 2 lines

r147482 | bgastudio | 2022-06-22 09:06:36 +0200 (Wed, 22 Jun 2022) | 2 lines

r147332 | bgastudio | 2022-06-21 09:03:29 +0200 (Tue, 21 Jun 2022) | 2 lines

r147331 | bgastudio | 2022-06-21 09:03:26 +0200 (Tue, 21 Jun 2022) | 2 lines

r147330 | bgastudio | 2022-06-21 09:03:21 +0200 (Tue, 21 Jun 2022) | 2 lines

r147184 | bgastudio | 2022-06-20 09:11:23 +0200 (Mon, 20 Jun 2022) | 2 lines

r146947 | bgastudio | 2022-06-18 09:02:29 +0200 (Sat, 18 Jun 2022) | 2 lines

r146888 | bgastudio | 2022-06-17 09:16:39 +0200 (Fri, 17 Jun 2022) | 2 lines

r146534 | bgastudio | 2022-06-14 09:02:46 +0200 (Tue, 14 Jun 2022) | 2 lines

r146533 | bgastudio | 2022-06-14 09:02:40 +0200 (Tue, 14 Jun 2022) | 2 lines

r145920 | bgastudio | 2022-06-09 09:02:56 +0200 (Thu, 09 Jun 2022) | 2 lines

r145919 | bgastudio | 2022-06-09 09:02:53 +0200 (Thu, 09 Jun 2022) | 2 lines

r145918 | bgastudio | 2022-06-09 09:02:50 +0200 (Thu, 09 Jun 2022) | 2 lines

r145134 | bgastudio | 2022-06-02 09:01:40 +0200 (Thu, 02 Jun 2022) | 2 lines

r145133 | bgastudio | 2022-06-02 09:01:38 +0200 (Thu, 02 Jun 2022) | 2 lines

r145113 | bgastudio | 2022-06-01 22:14:32 +0200 (Wed, 01 Jun 2022) | 2 lines

<Admin> New game (newyorkzoo)

------------------------------------------------------------------------
