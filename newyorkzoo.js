/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * NewYorkZoo implementation : © Séverine Kamycki severinek@gmail.com
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 *
 * newyorkzoo.js
 *
 * NewYorkZoo user interface script
 * 
 * In this file, you are describing the logic of your user interface, in Javascript language.
 *
 */
const CELL_WIDTH = 43;

class PatchManager {
    constructor(game) {
        console.log('patch manager constructor');
    }

    // on click hooks
    onClickPatch(event) {
        console.log('onClickPatch', event);
        var id = event.currentTarget.id;
        if (id == null) return;
        if (!this.beginPickPatch(id)) return;
        gameui.onUpdateActionButtons_client_PickPatch(gameui.gamedatas.gamestate.args);
    };

    onClickPatchControl(event) {
        event.preventDefault();
        //if (!gameui.onClickSanity(event, false)) return;
        this.triggerControl(event.currentTarget.id);
    };

    onSquare(event) {
        console.log('onSquare', event, gameui.isCurrentPlayerActive(), gameui.isPracticeMode());
        event.preventDefault();
        var id = event.currentTarget.id;
        if (!id) return;
        if (!gameui.isCurrentPlayerActive() && !gameui.isPracticeMode()) return;
        //		if (!gameui.isActiveSlot(id)) {
        //			if (!this.practiceMode)
        //				gameui.showError(_('Illegal patch location'));
        //		}
        const dropNode = this.getDropTarget(id);
        if (dropNode == null) return;
        if (dropNode == gameui.clientStateArgs.dropTarget) {
            // pick up the piece to move again
            this.beginPickPatch(this.selectedNode);
            this.selectPickPatchSquare(dropNode);
            return;
        }
        this.selectPickPatchSquare(dropNode);
        this.endPickPatch();
    };

    applyRotate(targetNode, dir) {
        if (gameui.clientStateArgs.rotateY === undefined) {
            gameui.clientStateArgs.rotateY = 0;
            gameui.clientStateArgs.rotateZ = 0;
        }
        gameui.clientStateArgs.rotateZ = this.normalizedRotateAngle(gameui.clientStateArgs.rotateZ);
        gameui.clientStateArgs.rotateY = this.normalizedRotateAngle(gameui.clientStateArgs.rotateY);
        if (!targetNode) return;

        if (dir) {
            let prevz = gameui.clientStateArgs.rotateZ - 90 * dir;
            $(targetNode).style.transition = 'none';
            $(targetNode).style.transform = "rotateY(" + gameui.clientStateArgs.rotateY + "deg) rotateZ(" + (prevz) + "deg)";
            $(targetNode).offsetHeight;
            $(targetNode).style.removeProperty("transition");
            $(targetNode).offsetHeight;
        }

        var state = this.getRotateState();
        gameui.changeTokenStateTo(targetNode, state);
        $(targetNode).style.removeProperty("transform");

        //$(targetNode).style.transform = "rotateY(" + gameui.clientStateArgs.rotateY + "deg) rotateZ(" + gameui.clientStateArgs.rotateZ + "deg)";
    };

    triggerControl(controlId) {
        console.log("trigger " + controlId);
        const targetNode = this.mobileNode;
        if (!this.mobileNode) {
            gameui.showError(_('Nothing is selected'));
            return;
        }
        if (controlId.startsWith('done_control')) {
            gameui.onDone();
            return;
        }
        if (controlId.startsWith('cancel_control')) {
            gameui.onCancel();
            return;
        }
        var dir = gameui.clientStateArgs.rotateY ? -1 : 1;
        var dirz = 0;
        if (controlId.startsWith('rotate_control')) {
            gameui.clientStateArgs.rotateZ = gameui.clientStateArgs.rotateZ + 90 * dir;
            dirz = dir;
        } else if (controlId.startsWith('rotateR_control')) {
            gameui.clientStateArgs.rotateZ = gameui.clientStateArgs.rotateZ - 90 * dir;
            dirz = -dir;
        } else if (controlId.startsWith('flipH_control')) {
            gameui.clientStateArgs.rotateZ = gameui.clientStateArgs.rotateZ + 180;

        } else if (controlId.startsWith('flip_control')) {

            if (gameui.clientStateArgs.rotateY)
                gameui.clientStateArgs.rotateY = 0;
            else
                gameui.clientStateArgs.rotateY = 180;
            //console.log("rotateY "+gameui.clientStateArgs.rotateY);
        }
        if (targetNode)
            this.applyRotate(targetNode, dirz);
        //
        this.updateActiveSquares();
    };



    // utils
    setupDragAndDropSupport() {
        // empty image hack to not have native ghost image
        this.emptyimg = new Image();
        this.emptyimg.src = 'data:image/gif;base64,R0lGODlhAQABAIAAAAUEBAAAACwAAAAAAQABAAACAkQBADs=';
        this.gamebody = $('ebd-body');

        var pboard = $('pboard_' + gameui.player_color);
        this.createPatchControl('rotate_control', pboard, "_a");
        this.createPatchControl('flip_control', pboard, "_a");
        this.createPatchControl('done_control', pboard, "_a");
        this.createPatchControl('cancel_control', pboard, "_a");

        const patchQuery = document.querySelectorAll('.patch');
        for (const item of patchQuery) {
            this.addDragListeners(item, false);
            item.addEventListener("click", event => this.onClickPatch(event), false)
        }

        document.querySelectorAll(".tableau_" + gameui.player_color + " .square")
            .forEach(item => {
                item.classList.add("drop-zone");
                item.addEventListener("click", event => this.onSquare(event), false)
            });


        document.querySelectorAll(".drop-zone").forEach(item => this.addDropListeners(item, false));
        this.addDropListeners($('thething'), false); // this is everyting else to show ghost image


    };

    addDragListeners(item, useCapture) {
        item.addEventListener("dragstart", event => this.dragStart(event), useCapture);
        item.addEventListener("dragend", event => this.dragEnd(event), useCapture);
        item.draggable = true;
        dojo.query(">*", item).forEach((node) => { node.draggable = false });
    };

    addDropListeners(item, useCapture) {
        item.addEventListener("dragover", event => this.dragOver(event), useCapture);
        item.addEventListener("dragenter", event => this.dragEnter(event), useCapture);
        //item.addEventListener("dragleave", event => this.dragLeave(event), useCapture);
        item.addEventListener("drop", event => this.dragDrop(event), useCapture);
    };

    dragStart(event) {
        //console.log("drag started ", event.target);
        if (!event.target.id) return;
        //event.preventDefault();// does not with with prevent defaults
        //event.stopPropagation();

        var targetNode = event.target;
        event.dataTransfer.setDragImage(this.emptyimg, CELL_WIDTH / 2, CELL_WIDTH / 2); // hide native ghost image
        this.gamebody.classList.add('pick-dragging');
        //debugger;
        var tokenId = this.beginPickPatch(targetNode);
        if (!tokenId) {
            this.gamebody.classList.remove('pick-dragging');
            event.dataTransfer.clearData();
            event.preventDefault();
            event.stopPropagation();
            return;
        }
        var shadowNode = this.createShadowNode(targetNode, 'pieces_' + gameui.player_color);
        this.applyRotate(this.mobileNode);
        gameui.moveClass('selected', this.selectedNode);
        shadowNode.style.setProperty("pointer-events", 'none');



        setTimeout(() => {
            var node = $(tokenId);
            var order = parseInt(node.getAttribute('data-order'));
            gameui.placeTokenLocal(tokenId, 'market', order, { noa: true });
        }, 100);
        //console.log("drag shadow", this.mobileNode.style);
        event.dataTransfer.setData("text/plain", tokenId); // not sure if needed
        event.dataTransfer.effectAllowed = "move";




    };

    dragEnd() {
        //		console.log("drag end");
        var shadowNode = $('dragShadow');
        if (!shadowNode) return;
        if (gameui.clientStateArgs.dropTarget) {
            this.selectPickPatchSquare($(gameui.clientStateArgs.dropTarget));
        }
        gameui.removeClass('pick-dragging');
        gameui.removeClass('drag_hover');

        //shadowNode.classList.remove('invalid');
        shadowNode.style.removeProperty("pointer-events");

        if (!gameui.clientStateArgs.dropTarget) {
            // cancel
            //console.log("end cancel");
            this.cancelPickPatch();
        } else {

            var state = this.getRotateState();
            gameui.placeTokenLocal(gameui.clientStateArgs.token, gameui.clientStateArgs.dropTarget, state, { noa: true });
            dojo.destroy(shadowNode);
            this.mobileNode = this.selectedNode;
            this.selectPickPatchSquare($(gameui.clientStateArgs.dropTarget));
            //console.log("end commit");
        }
        if (gameui.scrollmap)
            gameui.scrollmap.enableScrolling();
        this.endPickPatch();
    };

    getRotateState() {
        var state = this.normalizedRotateAngle(gameui.clientStateArgs.rotateZ) / 90 + this.normalizedRotateAngle(gameui.clientStateArgs.rotateY) / 180 * 4;
        return state;
    };

    dragEnter(event) {
        event.preventDefault();

        var dropNode = this.getDropTarget(event.currentTarget);
        if (dropNode) event.stopPropagation();
        //if (!dropNode) dropNode = this.getDropTarget(event.target);
        //console.log("enter " + event.target.id, event.currentTarget.id, dropNode);

        if (!this.mobileNode) return;// ???
        if (event.target.id == 'dragShadow') return;//ignore
        this.mobileNode.classList.remove('invalid');
        gameui.removeClass('drag_hover');


        if (dropNode == null) {
            return;
        };

        // draggig over flip controls
        if (dropNode.classList.contains('control-node')) {
            this.triggerControl(dropNode.id);
            return;
        }
        if (dropNode.id.startsWith('square')) {
            this.selectPickPatchSquare(dropNode);
        }
    };



    dragDrop(event) {
        event.preventDefault();
        event.stopPropagation();
        if (!this.mobileNode) return;
        var dropNode = this.getDropTarget(event.currentTarget);
        //console.log("drop this "+ this.selectedNode.id, dropNode,"event.target", event.target,"event.ctarget", event.currentTarget);
        //if (dropNode == null) {
        //	dropNode = $('pickTarget');
        //}
        if (dropNode == null) {
            gameui.clientStateArgs.dropTarget = null;
            return;
        }


        if (dropNode.id.startsWith('square')) {
            this.selectPickPatchSquare(dropNode);
            return;
        }
        // will be cancelled by endDrag
    };


    dragOver(event) {
        event.preventDefault();
        this.updateDragShadow(event);
    };

    dragLeave(event) {
        event.preventDefault();
        //console.log("leave "+this.id);
        const dropNode = this.getDropTarget(event.currentTarget);
        if (dropNode == null) return;

        //...
    };

    beginPickPatch(targetNode) {
        // 1) we selected original patch
        // 1.1) shadow exists
        // 2) we sekected drag shadow
        targetNode = $(targetNode);
        this.practiceMode = gameui.isPracticeMode();
        //console.log("begin "+targetNode.id);



        if (!this.practiceMode) {
            var has_error = true;
            var moves_info = gameui.gamedatas.gamestate.args.patches[targetNode.id];
            dojo.query('.head_error').forEach(dojo.destroy);// remove stack of error popups

            if (!gameui.isCurrentPlayerActive()) {
                gameui.showError(_("This is not your turn, turn on Practice Mode to practice placing"));
            } else if (!moves_info) {
                gameui.showError(_('You cannot select this patch yet'));
            } else if (!moves_info.canPay) {
                gameui.showError(_('You cannot afford this patch'));
            } else if (!moves_info.canPlace) {
                gameui.showError(_('You cannot place this patch on your quilt board, it would not fit'));
            } else {
                has_error = false;
            }
            if (has_error)
                return null;
        }


        gameui.clientStateArgs.token = targetNode.id;

        this.gamebody.classList.add('pick-activated');
        if (this.gamebody.classList.contains('pick-dragging') && gameui.scrollmap)
            gameui.scrollmap.disableScrolling();
        gameui.onUpdateActionButtons_client_PickPatch(gameui.gamedatas.gamestate.args);

        gameui.moveClass('selected', targetNode);
        if (targetNode != this.selectedNode) {
            if (targetNode != this.selectedNode && this.selectedNode) {


                if (this.practiceMode) {
                    this.selectedNode = null;
                }
            }
            this.cancelPickPatch();
            gameui.clientStateArgs.token = targetNode.id;
            // shadow clone will apear where it will be snapped
            //console.log("creating new shadow for "+targetNode.id);

            this.selectedNode = targetNode;
            this.mobileNode = targetNode;
            gameui.clientStateArgs.rotateZ = 0;
            gameui.clientStateArgs.rotateY = 0;

            var token = targetNode.id;
            dojo.destroy(token + "_temp");

            if (!token.startsWith("patch_0")) {
                var tokenOrig = dojo.clone($(token));
                tokenOrig.id = token + "_temp";
                tokenOrig.classList.remove('active_slot');
                tokenOrig.classList.add('original');


                dojo.place(tokenOrig, 'market');
                var order = parseInt(tokenOrig.getAttribute('data-order'));
                gameui.placeTokenLocal(tokenOrig.id, 'market', order - 1, { noa: true });
            }
            this.applyRotate(this.mobileNode);
            targetNode.style.transition = "none";
            gameui.attachToNewParentNoDestroy(targetNode, 'pieces_' + gameui.player_color);
            targetNode.style.removeProperty("transition");
            gameui.moveClass('selected', targetNode);

            this.updateActiveSquares();
        }

        //console.log("selected "+gameui.clientStateArgs.token);
        return gameui.clientStateArgs.token;
    };

    endPickPatch() {
        gameui.removeClass('pick-activated');
        if (gameui.scrollmap)
            gameui.scrollmap.enableScrolling();
        gameui.onUpdateActionButtons_client_PickPatch(gameui.gamedatas.gamestate.args);
    };

    cancelPickPatch() {
        if (this.selectedNode) {
            this.restoreOriginalPatch(this.selectedNode.id);
        }
        this.destroy('dragShadow');
        gameui.removeClass('selected');
        gameui.removeClass('pickTarget');
        gameui.clientStateArgs.rotateY = 0;
        gameui.clientStateArgs.rotateZ = 0;
        gameui.clientStateArgs.dropTarget = null;
        gameui.clientStateArgs.token = null;
        this.selectedNode = null;
    };

    restoreOriginalPatch(targetNode) {
        targetNode = $(targetNode);
        dojo.destroy(targetNode.id + "_temp");
        if (!targetNode.id.startsWith("patch_0")) {
            //var order = parseInt(targetNode.getAttribute('data-order'));
            dojo.place(targetNode.id, 'market');
            gameui.adjustScrollMap();
        } else {
            dojo.place(targetNode, 'tableau_' + gameui.player_color);
            gameui.stripPosition(targetNode);
        }
    };

    selectPickPatchSquare(dropNode) {
        if (!this.mobileNode) return;
        gameui.removeClass('invalid');
        if (!gameui.isActiveSlot(dropNode.id)) {
            this.mobileNode.classList.add('invalid');
        }
        gameui.positionObjectDirectly(this.mobileNode, dropNode.style.left, dropNode.style.top);

        gameui.moveClass('pickTarget', dropNode);
        gameui.moveClass('drag_hover', dropNode);
        gameui.clientStateArgs.dropTarget = dropNode.id;
        //console.log("pos",this.mobileNode.style.left, this.mobileNode.style.top);
    };

    getDropTarget(node) {
        if (!node) return null;
        node = $(node);
        if (node.classList && node.classList.contains('drop-zone')) {
            return node;
        }
        if (node.id === 'thething') return null;
        return this.getDropTarget(node.parentNode);
    };

    normalizedRotateAngle(angle) {
        if (!angle) return 0;
        angle = angle % 360;
        if (angle > 0) return angle;
        if (angle < 0) return 360 + angle;
        return 0;
    };

    updateActiveSquares() {
        document.querySelectorAll(".square.drop-zone").forEach(item => item.classList.remove("active_slot"));
        var curpatch = gameui.clientStateArgs.token;

        if (!curpatch) return;
        var combo = this.normalizedRotateAngle(gameui.clientStateArgs.rotateZ) + "_" + this.normalizedRotateAngle(gameui.clientStateArgs.rotateY);
        //console.log("updating moves for " + curpatch + " " + combo);
        var moves_info = gameui.gamedatas.gamestate.args.patches[curpatch];
        if (moves_info) {
            var moves = moves_info.moves[combo];
            if (moves) {
                for (var x of moves) {
                    $(x).classList.add("active_slot");
                }
            }
        }


        const dropNode = document.querySelector(".pickTarget");
        const targetNode = this.selectedNode;

        if (dropNode && targetNode) {
            targetNode.classList.remove('invalid');
            if (!gameui.isActiveSlot(dropNode.id)) {
                targetNode.classList.add('invalid');
            }
        }

    };

    destroy(str) {
        var elem = $(str);
        if (elem) elem.remove();
    };

    updateDragShadow(coords) {
        const shadowNode = $('dragShadow');
        if (!shadowNode) {
            return;
        }

        var top, left;

        var dropNode = document.querySelector('.drag_hover')
        if (dropNode) {
            shadowNode.style.left = dropNode.style.left;
            shadowNode.style.top = dropNode.style.top;
            //console.log("pos snap", this.mobileNode.style.left, this.mobileNode.style.top);
        } else if (shadowNode.parentNode) {
            var rectM = shadowNode.parentNode.getBoundingClientRect();
            left = (coords.clientX - rectM.x - CELL_WIDTH / 2);
            top = (coords.clientY - rectM.y - CELL_WIDTH / 2);
            shadowNode.style.left = left + "px";
            shadowNode.style.top = top + "px";

            //console.log("pos free", shadowNode.style.left, shadowNode.style.top, coords);
        }

    };

    createShadowNode(targetNode, parent) {
        this.mobileNodeParent = $(parent);
        this.destroy('dragShadow');
        var rectT = targetNode.getBoundingClientRect();
        var rectM = this.mobileNodeParent.getBoundingClientRect();
        var x = rectT.x - rectM.x; //x position within the element.
        var y = rectT.y - rectM.y;  //y position within the element.
        //		console.log("dist "+y,rectT, rectM);

        var shadowNode = this.mobileNode = targetNode.cloneNode(true);
        shadowNode.id = 'dragShadow';
        shadowNode.style.transition = 'none';

        //var shadowNode = this.mobileNode = targetNode;
        dojo.setAttr(shadowNode, "data-from", targetNode.id);

        shadowNode.classList.remove('active_slot', 'original');
        shadowNode.classList.add('drag-shadow');
        gameui.stripPosition(shadowNode);
        shadowNode.style.removeProperty("transform");

        //gameui.attachToNewParentNoDestroy(shadowNode,this.mobileNodeParent);
        shadowNode.style.position = 'absolute';

        this.mobileNodeParent.appendChild(shadowNode);
        shadowNode.style.left = x + "px";
        shadowNode.style.top = y + "px";
        shadowNode.style.removeProperty("transition");

        //this.addDragListeners(shadowNode, false);
        //shadowNode.addEventListener("click", event => this.onClickPatch(event), false)

        return shadowNode;
    };

    createPatchControl(id, targetNode, postfix) {
        if (!postfix)
            postfix = '';
        var nid = id + postfix;
        gameui.createToken(nid, null, targetNode, event => this.onClickPatchControl(event));
        gameui.updateTooltip(nid);

        return $(nid);
    };

};

define([
    "dojo", "dojo/_base/declare",
    "ebg/core/gamegui",
    "ebg/counter",

    g_gamethemeurl + '/modules/sharedparent.js', // custom module
    g_gamethemeurl + '/modules/extscrollmap.js',
],
    function (dojo, declare) {
        return declare("bgagame.newyorkzoo", bgagame.sharedparent, {
            constructor: function () {
                console.log('newyorkzoo constructor');
                this.pm = new PatchManager(this);
                // Here, you can init the global variables of your user interface
                // Example:
                // this.myGlobalValue = 0;

            },

            /*
                setup:
                
                This method must set up the game user interface according to current game situation specified
                in parameters.
                
                The method is called each time the game interface is displayed to a player, ie:
                _ when the game starts
                _ when a player refreshes the game page (F5)
                
                "gamedatas" argument contains all datas retrieved by your "getAllDatas" PHP method.
            */

            setup: function (gamedatas) {
                console.log("Starting game setup", gamedatas);
                var playerCount=Object.keys(gamedatas.players).length;

                // Setting up player boards
                document.documentElement.style.setProperty('--colsNb', gamedatas.gridSize[0]);
                document.documentElement.style.setProperty('--rowsNb', gamedatas.gridSize[1]);
                document.documentElement.style.setProperty('--playerCount', playerCount);

                for (let index = 1; index <= 5; index++) {
                    if (i != playerCount) {
                        this.dontPreloadImage('board'+playerCount+'P.png');
                    }
                }

                for (var player_id in gamedatas.players) {
                    var player = gamedatas.players[player_id];

                    // TODO: Setting up players boards if needed
                }



                // TODO: Set up your game interface here, according to "gamedatas"

                this.inherited(arguments);
                // Setup game notifications to handle (see "setupNotifications" method below)
                this.setupNotifications();

                console.log("Ending game setup");
            },

            setupScrollableMap: function () {
                // Scrollable area  for pieces      	
                this.scrollmap = new bgagame.extscrollmap();
                // Make map scrollable        	
                this.scrollmap.create($('map_container'), $('map_scrollable'), $('map_surface'), $('market'));
                this.scrollmap.only_x = true;
                this.scrollmap.setupOnScreenArrows(150);
                this.scrollmap.setPos(0, 0);
                document.addEventListener("blur", (e) => {
                    // Cancel my drag and drop here
                    this.scrollmap.onMouseUp(e);
                });
            },

            setupToken: function (token, tokenRec) {
                if (token.startsWith('patch')) {
                    var tokenId = token;//tokens with several occurrences should be found by their type id
                    if (token.split("_").length == 3) {
                        tokenId = token.substring(0, token.lastIndexOf("_"));
                    }
                    var mat = this.gamedatas.token_types[tokenId];

                    //					var tokenInfo = this.getTokenDisplayInfo(token);
                    //					$(token).title = this.format_string_recursive(
                    //						tokenInfo.title.log,
                    //						tokenInfo.title.args);

                    var dx = Math.floor((Math.random() * CELL_WIDTH));
                    var dy = Math.floor((Math.random() * CELL_WIDTH * (4 - mat.h)));
                    var rotateZ = Math.floor((Math.random() * 20 - 10));
                    dojo.setAttr(token, { "data-order": this.getTokenState(token), "data-dx": dx, "data-dy": dy, "data-rz": rotateZ });
                } else if (token == 'token_neutral') {
                    var dx = CELL_WIDTH;
                    var dy = CELL_WIDTH * 2;
                    var rotateZ = 0;
                    dojo.setAttr(token, { "data-order": this.getTokenState(token), "data-dx": dx, "data-dy": dy, "data-rz": rotateZ });
                }
            },
            setupGameTokens: function () {

                this.setupScrollableMap();

                //this.setupPreference();
                //this.updateCountersSafe(this.gamedatas.counters);
                //this.updateMyCountersAll();
                for (var token in this.gamedatas.tokens) {
                    var tokenInfo = this.gamedatas.tokens[token];
                    var location = tokenInfo.location;
                    if (!$(location) && this.gamedatas.tokens[location]) {
                        this.placeTokenWithTips(location);
                    }
                    this.placeTokenWithTips(token);

                }

                for (var token in this.gamedatas.tokens) {
                    var tokenInfo = this.gamedatas.tokens[token];
                    this.setupToken(token, tokenInfo);
                }
                this.updateCountersSafe(this.gamedatas.counters);
                dojo.query('.mini_counter').forEach((node) => {
                    this.updateTooltip(node.id, node.parentNode);
                });

                this.pm.setupDragAndDropSupport();

                this.connectClass('timetracker', 'onclick', 'onTimeTracker');
                dojo.query(".timetracker").forEach((node) => {
                    this.updateTooltip(node.id);
                });
                //this.connectClass('token_neutral', 'onclick', 'onZoomPlus');
                this.notif_eofnet();



                // DEBUG BUTTON
                var parent = this.queryFirst('.debug_section');
                if (parent) {
                    var butt = dojo.create('a', { class: 'bgabutton bgabutton_gray', innerHTML: "Reload CSS" }, parent);
                    dojo.connect(butt, 'onclick', () => reloadCss());
                }

                console.log("enging token setup");
            },
            adjustScrollMap: function (duration) {
                // we need to move market cursor to center on 3 pieces to select
                if (!$('thething')) return;
                var container = $('market').parentNode;
                if (!container && !this.scrollmap) return;
                var width = container.offsetWidth;
                $('thething').style.removeProperty('height');
                var height = container.offsetHeight;

                var pawnPos = parseInt(gameui.getTokenState('token_neutral'));
                var TOTAL = 34;
                var off = 0;// TOTAL - pawnPos;

                var patchList = Array.from(document.querySelectorAll("#market > *")).sort(function (a, b) {
                    var aLeft = (off + parseInt(a.getAttribute('data-order'))) % TOTAL;
                    var bLeft = (off + parseInt(b.getAttribute('data-order'))) % TOTAL;
                    return aLeft - bLeft;
                });

                var dirx = 1;
                var diry = 0;
                var opa = 1;

                for (var order = 0; order < patchList.length; order++) {
                    var item = patchList[order];

                    var info = gameui.getTokenDisplayInfo(item.id)
                    var prevInfo = null;
                    if (order > 0) {
                        var previtem = patchList[order - 1];
                        prevInfo = gameui.getTokenDisplayInfo(previtem.id);
                        if (!prevInfo) console.error("Missing prev patch for " + (order));
                    }

                    var tokenNode = $(info.key);
                    //console.log("layout "+info.key+" "+order);
                    var dx = parseInt(dojo.getAttr(tokenNode, "data-dx")) || 0;
                    var dy = parseInt(dojo.getAttr(tokenNode, "data-dy")) || 0;
                    var mwidth = 0;
                    var mheight = 0;

                    if (prevInfo) {
                        var prevNode = $(prevInfo.key);

                        var px = parseInt(dojo.getAttr(prevNode, "data-x")) || 0;
                        var py = parseInt(dojo.getAttr(prevNode, "data-y")) || 0;
                        var pw = parseInt(prevInfo.w);
                        var ph = parseInt(prevInfo.h);
                        //console.log(tokenNode.id + " order " + order + " prevx=" + px + " w=" + prevInfo.w);

                        var mwidth = px + pw * CELL_WIDTH + CELL_WIDTH;
                        var mheight = 0;
                        if (item.id != 'token_neutral') dx = 0;
                    }
                    tokenNode.style.left = (mwidth + dx) + "px";
                    tokenNode.style.top = (mheight + dy) + "px";
                    var rotateZ = parseInt(dojo.getAttr(tokenNode, "data-rz"));
                    if (rotateZ) {
                        var rule = "rotateY(0deg) rotateZ(" + rotateZ + "deg)";
                        tokenNode.style.transform = rule;
                    }
                    if (opa != 1) tokenNode.style.opacity = opa;
                    else tokenNode.style.removeProperty('opacity');
                    dojo.setAttr(tokenNode, "data-x", mwidth);
                    dojo.setAttr(tokenNode, "data-y", mheight);
                };

                if (this.scrollmap) {


                    var mwidth = CELL_WIDTH * 4 * pawnPos;

                    var sect = CELL_WIDTH * 5 * 3;
                    var half = (width - sect) / 2;


                    if (half <= 0) {
                        this.scrollmap.setPos(-mwidth, 0);
                    } else {
                        //scroll map to 0,0
                        this.scrollmap.setPos(0, 0);
                    }
                }
            },

            ///////////////////////////////////////////////////
            //// Game & client states

            // onEnteringState: this method is called each time we are entering into a new game state.
            //                  You can use this method to perform some user interface changes at this moment.
            //
            onEnteringState: function (stateName, args) {
                console.log('Entering state: ' + stateName);

                switch (stateName) {

                    /* Example:
                    
                    case 'myGameState':
                    
                        // Show some HTML block at this game state
                        dojo.style( 'my_html_block_id', 'display', 'block' );
                        
                        break;
                   */


                    case 'dummmy':
                        break;
                }
            },

            // onLeavingState: this method is called each time we are leaving a game state.
            //                 You can use this method to perform some user interface changes at this moment.
            //
            onLeavingState: function (stateName) {
                console.log('Leaving state: ' + stateName);

                switch (stateName) {

                    /* Example:
                    
                    case 'myGameState':
                    
                        // Hide the HTML block we are displaying only during this game state
                        dojo.style( 'my_html_block_id', 'display', 'none' );
                        
                        break;
                   */


                    case 'dummmy':
                        break;
                }
            },

            // onUpdateActionButtons: in this method you can manage "action buttons" that are displayed in the
            //                        action status bar (ie: the HTML links in the status bar).
            //        
            onUpdateActionButtons: function (stateName, args) {
                console.log('onUpdateActionButtons: ' + stateName);

                if (this.isCurrentPlayerActive()) {
                    switch (stateName) {
                        /*               
                                         Example:
                         
                                         case 'myGameState':
                                            
                                            // Add 3 action buttons in the action status bar:
                                            
                                            this.addActionButton( 'button_1_id', _('Button 1 label'), 'onMyMethodToCall1' ); 
                                            this.addActionButton( 'button_2_id', _('Button 2 label'), 'onMyMethodToCall2' ); 
                                            this.addActionButton( 'button_3_id', _('Button 3 label'), 'onMyMethodToCall3' ); 
                                            break;
                        */
                    }
                }
            },

            ///////////////////////////////////////////////////
            //// Utility methods

            /*
            
                Here, you can defines some utility methods that you can use everywhere in your javascript
                script.
            
            */


            ///////////////////////////////////////////////////
            //// Player's action

            /*
            
                Here, you are defining methods to handle player's action (ex: results of mouse click on 
                game objects).
                
                Most of the time, these methods:
                _ check the action is possible at this game state.
                _ make a call to the game server
            
            */

            /* Example:
            
            onMyMethodToCall1: function( evt )
            {
                console.log( 'onMyMethodToCall1' );
                
                // Preventing default browser reaction
                dojo.stopEvent( evt );
    
                // Check that this action is possible (see "possibleactions" in states.inc.php)
                if( ! this.checkAction( 'myAction' ) )
                {   return; }
    
                this.ajaxcall( "/newyorkzoo/newyorkzoo/myAction.html", { 
                                                                        lock: true, 
                                                                        myArgument1: arg1, 
                                                                        myArgument2: arg2,
                                                                        ...
                                                                     }, 
                             this, function( result ) {
                                
                                // What to do after the server call if it succeeded
                                // (most of the time: nothing)
                                
                             }, function( is_error) {
    
                                // What to do after the server call in anyway (success or failure)
                                // (most of the time: nothing)
    
                             } );        
            },        
            
            */


            ///////////////////////////////////////////////////
            //// Reaction to cometD notifications

            /*
                setupNotifications:
                
                In this method, you associate each of your game notifications with your local method to handle it.
                
                Note: game notification names correspond to "notifyAllPlayers" and "notifyPlayer" calls in
                      your newyorkzoo.game.php file.
            
            */
            setupNotifications: function () {
                console.log('notifications subscriptions setup');

                // TODO: here, associate your game notifications with local methods

                // Example 1: standard notification handling
                // dojo.subscribe( 'cardPlayed', this, "notif_cardPlayed" );

                // Example 2: standard notification handling + tell the user interface to wait
                //            during 3 seconds after calling the method in order to let the players
                //            see what is happening in the game.
                // dojo.subscribe( 'cardPlayed', this, "notif_cardPlayed" );
                // this.notifqueue.setSynchronous( 'cardPlayed', 3000 );
                // 
            },

            // TODO: from this point and below, you can write your game notifications handling methods

            /*
            Example:
            
            notif_cardPlayed: function( notif )
            {
                console.log( 'notif_cardPlayed' );
                console.log( notif );
                
                // Note: notif.args contains the arguments specified during you "notifyAllPlayers" / "notifyPlayer" PHP call
                
                // TODO: play the card in the user interface.
            },    
            
            */
            notif_eofnet: function (notif) {

                this.adjustScrollMap();
                if (notif && notif.args.income) {

                    var loc = 'income_' + notif.args.player_color + "_counter";
                    var counter = $(loc);
                    //console.log('income counter update ' + loc, notif.args);
                    if (counter) counter.innerHTML = notif.args.income;
                    else console.error("cannot find player counter " + loc);
                    gameui.gamedatas.counters[loc] = { counter_value: notif.args.income, counter_name: loc };
                }
            },
        });
    });
