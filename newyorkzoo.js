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
const CELL_WIDTH = 42;

class PatchManager {
    constructor(game) {
        debug('patch manager constructor');
    }

    // on click hooks
    onClickPatch(event) {
        debug('onClickPatch', event);
        var id = event.currentTarget.id;
        if (id == null) return;
        if (gameui.curstate === 'placeAnimal' || gameui.curstate === 'client_PlaceAnimal') return;
        id = this.firefoxWorkaroundLastMonominoNotOnTop(event.currentTarget);
        if (!this.beginPickPatch(id)) return;
        gameui.onUpdateActionButtons_client_PickPatch(gameui.gamedatas.gamestate.args);
    }

    onClickPatchControl(event) {
        event.preventDefault();
        //if (!gameui.onClickSanity(event, false)) return;
        this.triggerControl(event.currentTarget.id);
    }

    onSquare(event) {
        var id = event.currentTarget.id;
        debug('onSquare', id, event, gameui.isCurrentPlayerActive(), gameui.isPracticeMode(), gameui.curstate);
        event.preventDefault();
        if (!id) return;
        if (!gameui.isCurrentPlayerActive() && !gameui.isPracticeMode()) return;
        //		if (!gameui.isActiveSlot(id)) {
        //			if (!this.practiceMode)
        //				gameui.showError(_('Illegal patch location'));
        //		}
        const dropNode = this.getDropTarget(id);
        if (dropNode == null) return;

        if (gameui.curstate === 'client_PlaceAnimal') {
            gameui.clientStateArgs.to = gameui.replaceGridSquareByAnimalSquare(dropNode.id);
            gameui.ajaxClientStateAction();
        } else if (gameui.curstate === 'populateNewFence') {
            $(id).classList.toggle('selected');
            gameui.clientStateArgs.to = gameui.replaceGridSquareByAnimalSquare(id);
            if (gameui.clientStateArgs.from && gameui.clientStateArgs.to) {
                gameui.ajaxClientStateAction();
            }
            //gameui.startActionTimer("place_animal", 3, 1);
        } else if (gameui.curstate === 'chooseFence') {
            dojo.toggleClass(dropNode.id, 'animal-target-image');
            gameui.clientStateArgs.squares = gameui
                .queryIds('.animal-target-image')
                .map((sqre) => gameui.replaceGridSquareByAnimalSquare(sqre));
            debug('gameui.clientStateArgs.squares', gameui.clientStateArgs.squares);
        } else {
            if (dropNode == gameui.clientStateArgs.dropTarget) {
                // pick up the piece to move again
                this.beginPickPatch(this.selectedNode);
                this.selectPickPatchSquare(dropNode);
                return;
            }
            const fence = gameui.queryFirst('.selected');
            //if (gameui.isPracticeMode() || (fence && fence.hasOwnProperty('draggable') && fence.draggable)) {
            this.selectPickPatchSquare(dropNode);
            this.endPickPatch();
            //}
        }
    }

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
            $(targetNode).style.transform =
                'rotateY(' + gameui.clientStateArgs.rotateY + 'deg) rotateZ(' + prevz + 'deg)';
            $(targetNode).offsetHeight;
            $(targetNode).style.removeProperty('transition');
            $(targetNode).offsetHeight;
        }

        var state = this.getRotateState();
        gameui.changeTokenStateTo(targetNode, state);
        $(targetNode).style.removeProperty('transform');

        //$(targetNode).style.transform = "rotateY(" + gameui.clientStateArgs.rotateY + "deg) rotateZ(" + gameui.clientStateArgs.rotateZ + "deg)";
    }

    triggerControl(controlId) {
        debug('trigger ' + controlId);
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
            if (gameui.clientStateArgs.rotateY) gameui.clientStateArgs.rotateY = 0;
            else gameui.clientStateArgs.rotateY = 180;
            //debug("rotateY "+gameui.clientStateArgs.rotateY);
        }
        if (targetNode) this.applyRotate(targetNode, dirz);
        //
        this.updateActiveSquares();
    }

    // utils
    setupDragAndDropSupport() {
        // empty image hack to not have native ghost image
        this.emptyimg = new Image();
        this.emptyimg.src = 'data:image/gif;base64,R0lGODlhAQABAIAAAAUEBAAAACwAAAAAAQABAAACAkQBADs='; //1x1px black GIF
        this.gamebody = $('ebd-body');

        var pboard = $('tableau_' + gameui.player_no);
        this.createPatchControl('rotate_control', pboard, '_a');
        this.createPatchControl('flip_control', pboard, '_a');
        this.createPatchControl('done_control', pboard, '_a');
        this.createPatchControl('cancel_control', pboard, '_a');

        const patchQuery = document.querySelectorAll('.market .patch, .bonus_market .patch');
        for (const item of patchQuery) {
            this.addDragListeners(item, false);
            item.addEventListener('click', (event) => this.onClickPatch(event), false);
        }

        document.querySelectorAll('.tableau_' + gameui.player_no + ' .square').forEach((item) => {
            item.classList.add('drop-zone');
            item.addEventListener('click', (event) => this.onSquare(event), false);
        });

        document.querySelectorAll('.drop-zone').forEach((item) => this.addDropListeners(item, false));
        this.addDropListeners($('thething'), false); // this is everyting else to show ghost image
    }

    addDragListeners(item, useCapture) {
        item.addEventListener('dragstart', (event) => this.dragStart(event), useCapture);
        item.addEventListener('dragend', (event) => this.dragEnd(event), useCapture);
        item.draggable = true;
        dojo.query('>*', item).forEach((node) => {
            node.draggable = false;
        });
    }

    addDropListeners(item, useCapture) {
        item.addEventListener('dragover', (event) => this.dragOver(event), useCapture);
        item.addEventListener('dragenter', (event) => this.dragEnter(event), useCapture);
        item.addEventListener('dragleave', (event) => this.dragLeave(event), useCapture);
        item.addEventListener('drop', (event) => this.dragDrop(event), useCapture);
    }

    dragStart(event) {
        debug('drag started ', event.target);
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
        var shadowNode = this.createShadowNode(targetNode, 'pieces_' + gameui.player_no);
        this.applyRotate(this.mobileNode);
        gameui.moveClass('selected', this.selectedNode);
        shadowNode.style.setProperty('pointer-events', 'none');

        setTimeout(() => {
            var node = $(tokenId);
            var order = parseInt(node.getAttribute('data-order'));
            gameui.placeTokenLocal(tokenId, 'market', order, { noa: true });
            // gameui.placeTokenLocal(tokenId, "hand_"+gameui.player_id, order, { noa: true })
        }, 100);
        //debug("drag shadow", this.mobileNode.style);
        event.dataTransfer.setData('text/plain', tokenId); // not sure if needed
        event.dataTransfer.effectAllowed = 'move';
    }

    dragEnd() {
        debug('drag end');
        var shadowNode = $('dragShadow');
        if (!shadowNode) return;
        if (gameui.clientStateArgs.dropTarget) {
            this.selectPickPatchSquare($(gameui.clientStateArgs.dropTarget));
        }
        gameui.removeClass('pick-dragging');
        gameui.removeClass('drag_hover');

        //shadowNode.classList.remove('invalid');
        shadowNode.style.removeProperty('pointer-events');

        if (!gameui.clientStateArgs.dropTarget) {
            // cancel
            debug('end cancel');
            this.cancelPickPatch();
        } else {
            var state = this.getRotateState();
            gameui.placeTokenLocal(gameui.clientStateArgs.token, gameui.clientStateArgs.dropTarget, state, {
                noa: true,
            });
            dojo.destroy(shadowNode);
            this.mobileNode = this.selectedNode;
            this.selectPickPatchSquare($(gameui.clientStateArgs.dropTarget));
            //debug("end commit");
        }
        if (gameui.scrollmap) gameui.scrollmap.enableScrolling();
        this.endPickPatch();
    }

    getRotateState() {
        var state =
            this.normalizedRotateAngle(gameui.clientStateArgs.rotateZ) / 90 +
            (this.normalizedRotateAngle(gameui.clientStateArgs.rotateY) / 180) * 4;
        return state;
    }

    dragEnter(event) {
        event.preventDefault();

        var dropNode = this.getDropTarget(event.currentTarget);
        if (dropNode) event.stopPropagation();
        //if (!dropNode) dropNode = this.getDropTarget(event.target);
        //debug("enter " + event.target.id, event.currentTarget.id, dropNode);

        if (!this.mobileNode) return; // ???
        if (event.target.id == 'dragShadow') return; //ignore
        this.mobileNode.classList.remove('invalid');
        gameui.removeClass('drag_hover');

        if (dropNode == null) {
            return;
        }

        // draggig over flip controls
        if (dropNode.classList.contains('control-node')) {
            this.triggerControl(dropNode.id);
            return;
        }
        if (dropNode.id.startsWith('square')) {
            this.selectPickPatchSquare(dropNode);
        }
    }

    dragDrop(event) {
        event.preventDefault();
        event.stopPropagation();
        if (!this.mobileNode) return;
        var dropNode = this.getDropTarget(event.currentTarget);
        //debug("drop this "+ this.selectedNode.id, dropNode,"event.target", event.target,"event.ctarget", event.currentTarget);
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
    }

    dragOver(event) {
        event.preventDefault();
        this.updateDragShadow(event);
    }

    dragLeave(event) {
        event.preventDefault();
        //debug("leave "+this.id);
        const dropNode = this.getDropTarget(event.currentTarget);
        if (dropNode == null) return;

        //...
    }

    firefoxWorkaroundLastMonominoNotOnTop(clickTarget) {
        let replacedTarget = clickTarget;
        //if click on 1x1 square and exists other 1x1 square with .active-slot, simulate the click on the last one
        if (!clickTarget.classList.contains('active_slot') && clickTarget.parentNode.dataset?.maskGroup == ':1') {
            const shouldHaveBeenClickedInstead = gameui.queryFirstId(`#${clickTarget.parentNode.id} .patch.active_slot`);
            if (shouldHaveBeenClickedInstead) replacedTarget = shouldHaveBeenClickedInstead;
        }
        return replacedTarget;
    }

    beginPickPatch(targetNode) {
        // 1) we selected original patch
        // 1.1) shadow exists
        // 2) we sekected drag shadow
        targetNode = $(targetNode);
        this.practiceMode = gameui.isPracticeMode();
        var location = targetNode.parentNode.id;
        debug('begin ' + targetNode.id);

        if (!this.practiceMode) {
            var has_error = true;
            var moves_info = gameui.gamedatas.gamestate.args.patches[targetNode.id];
            dojo.query('.head_error').forEach(dojo.destroy); // remove stack of error popups

            if (!gameui.isCurrentPlayerActive()) {
                gameui.showError(_('This is not your turn, turn on Practice Mode to practice placing'));
            } else if (!moves_info) {
                gameui.showError(_('You cannot select this enclosure yet'));
            } else if (!moves_info.canPlace) {
                gameui.showError(_('You cannot place this enclosure on your zoo, it would not fit'));
            } else if (!moves_info.canUse) {
                gameui.showError(
                    _('You cannot place an enclosure on your zoo, you would have no animal to populate it')
                );
            } else {
                has_error = false;
            }
            if (has_error) return null;
        }

        gameui.clientStateArgs.token = targetNode.id;

        this.gamebody.classList.add('pick-activated');
        $('overall-content').classList.add('placingFence');

        if (this.gamebody.classList.contains('pick-dragging') && gameui.scrollmap) gameui.scrollmap.disableScrolling();
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
            debug('creating new shadow for ' + targetNode.id);

            this.selectedNode = targetNode;
            this.mobileNode = targetNode;
            gameui.clientStateArgs.rotateZ = 0;
            gameui.clientStateArgs.rotateY = 0;

            var token = targetNode.id;
            dojo.destroy(token + '_temp');

            if (!token.startsWith('patch_0')) {
                var tokenOrig = dojo.clone($(token));
                tokenOrig.id = token + '_temp';
                tokenOrig.classList.remove('active_slot');
                tokenOrig.classList.add('original');

                dojo.place(tokenOrig, location);
                var order = parseInt(tokenOrig.getAttribute('data-order'));
                gameui.placeTokenLocal(tokenOrig.id, location, order - 1, { noa: true });
            }
            this.applyRotate(this.mobileNode);
            targetNode.style.transition = 'none';
            debug('moved to pieces');
            gameui.attachToNewParentNoDestroy(targetNode, 'pieces_' + gameui.player_no);
            targetNode.style.removeProperty('transition');
            gameui.moveClass('selected', targetNode);

            this.updateActiveSquares();
        }

        //debug("selected "+gameui.clientStateArgs.token);
        return gameui.clientStateArgs.token;
    }

    endPickPatch() {
        gameui.removeClass('pick-activated');
        if (gameui.scrollmap) gameui.scrollmap.enableScrolling();
        gameui.onUpdateActionButtons_client_PickPatch(gameui.gamedatas.gamestate.args);
    }

    cancelPickPatch() {
        debug('cancelPickPatch', this.selectedNode);
        if (this.selectedNode) {
            this.restoreOriginalPatch(this.selectedNode.id);
            $('overall-content').classList.remove('placingFence');
        }
        this.destroy('dragShadow');
        gameui.removeClass('selected');
        gameui.removeClass('pickTarget');
        gameui.clientStateArgs.rotateY = 0;
        gameui.clientStateArgs.rotateZ = 0;
        gameui.clientStateArgs.dropTarget = null;
        gameui.clientStateArgs.token = null;
        this.selectedNode = null;
    }

    restoreOriginalPatch(targetNode) {
        targetNode = $(targetNode);
        dojo.destroy(targetNode.id + '_temp');
        debug('restoreOriginalPatch', $(targetNode));
        if (!targetNode.id.startsWith('patch_0')) {
            let dest = 'market';
            if (targetNode.dataset?.startFence == 'true') {
                dest = 'hand_' + gameui.player_id;
                gameui.stripPosition(targetNode);
            }
            //var order = parseInt(targetNode.getAttribute('data-order'));
            dojo.place(targetNode.id, dest);
            gameui.adjustScrollMap();
        } else {
            dojo.place(targetNode, 'tableau_' + gameui.player_no);
            gameui.stripPosition(targetNode);
        }
    }

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
        //debug("pos",this.mobileNode.style.left, this.mobileNode.style.top);
    }

    getDropTarget(node) {
        if (!node) return null;
        node = $(node);
        if (node.classList && node.classList.contains('drop-zone')) {
            return node;
        }
        if (node.id === 'thething') return null;
        return this.getDropTarget(node.parentNode);
    }

    normalizedRotateAngle(angle) {
        if (!angle) return 0;
        angle = angle % 360;
        if (angle > 0) return angle;
        if (angle < 0) return 360 + angle;
        return 0;
    }

    updateActiveSquares() {
        document
            .querySelectorAll('.square.drop-zone, .anml-square.active_slot, .highlight-square.active_slot')
            .forEach((item) => item.classList.remove('active_slot'));
        var curpatch = gameui.clientStateArgs.token;

        if (!curpatch) return;
        var combo =
            this.normalizedRotateAngle(gameui.clientStateArgs.rotateZ) +
            '_' +
            this.normalizedRotateAngle(gameui.clientStateArgs.rotateY);
        //debug('updating moves for ' + curpatch + ' ' + combo);
        var moves_info = gameui.gamedatas.gamestate.args.patches[curpatch];
        if (moves_info) {
            var moves = moves_info.moves[combo];
            if (moves) {
                for (var x of moves) {
                    $(x).classList.add('active_slot');
                    //todo there is still pbs with animals
                    //when target mark is on another patch, activate only square_x_x won’t be seen since this patch is on top of it
                    //so we activate anml_square_x_x too to make it visible
                    $(gameui.replaceGridSquareByHighlightSquare(x)).classList.add('active_slot');
                }
            }
        }

        const dropNode = document.querySelector('.pickTarget');
        const targetNode = this.selectedNode;

        if (dropNode && targetNode) {
            targetNode.classList.remove('invalid');
            if (!gameui.isActiveSlot(dropNode.id)) {
                targetNode.classList.add('invalid');
            }
        }
    }

    destroy(str) {
        var elem = $(str);
        if (elem) elem.remove();
    }

    updateDragShadow(coords) {
        const shadowNode = $('dragShadow');
        if (!shadowNode) {
            return;
        }

        var top, left;

        var dropNode = document.querySelector('.drag_hover');
        if (dropNode) {
            shadowNode.style.left = dropNode.style.left;
            shadowNode.style.top = dropNode.style.top;
            //debug("pos snap", this.mobileNode.style.left, this.mobileNode.style.top);
        } else if (shadowNode.parentNode) {
            var rectM = shadowNode.parentNode.getBoundingClientRect();
            left = coords.clientX - rectM.x - CELL_WIDTH / 2;
            top = coords.clientY - rectM.y - CELL_WIDTH / 2;
            shadowNode.style.left = left + 'px';
            shadowNode.style.top = top + 'px';

            //debug("pos free", shadowNode.style.left, shadowNode.style.top, coords);
        }
    }

    createShadowNode(targetNode, parent) {
        this.mobileNodeParent = $(parent);
        this.destroy('dragShadow');
        var rectT = targetNode.getBoundingClientRect();
        var rectM = this.mobileNodeParent.getBoundingClientRect();
        var x = rectT.x - rectM.x; //x position within the element.
        var y = rectT.y - rectM.y; //y position within the element.
        //		debug("dist "+y,rectT, rectM);

        var shadowNode = (this.mobileNode = targetNode.cloneNode(true));
        shadowNode.id = 'dragShadow';
        shadowNode.style.transition = 'none';

        //var shadowNode = this.mobileNode = targetNode;
        dojo.setAttr(shadowNode, 'data-from', targetNode.id);

        shadowNode.classList.remove('active_slot', 'original');
        shadowNode.classList.add('drag-shadow');
        gameui.stripPosition(shadowNode);
        shadowNode.style.removeProperty('transform');

        //gameui.attachToNewParentNoDestroy(shadowNode,this.mobileNodeParent);
        shadowNode.style.position = 'absolute';

        this.mobileNodeParent.appendChild(shadowNode);
        shadowNode.style.left = x + 'px';
        shadowNode.style.top = y + 'px';
        shadowNode.style.removeProperty('transition');

        this.addDragListeners(shadowNode, false);
        shadowNode.addEventListener('click', (event) => this.onClickPatch(event), false);

        return shadowNode;
    }

    createPatchControl(id, targetNode, postfix) {
        if (!postfix) postfix = '';
        var nid = id + postfix;
        gameui.createToken(nid, null, targetNode, (event) => this.onClickPatchControl(event));
        gameui.updateTooltip(nid);

        return $(nid);
    }
}

define([
    'dojo',
    'dojo/_base/declare',
    'ebg/core/gamegui',
    'ebg/counter',

    g_gamethemeurl + '/modules/sharedparent.js', // custom module
    g_gamethemeurl + '/modules/extscrollmap.js',
], function (dojo, declare) {
    return declare('bgagame.newyorkzoo', bgagame.sharedparent, {
        constructor: function () {
            debug('newyorkzoo constructor');
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
            debug('Starting game setup, gamedatas:', gamedatas);
            var playerCount = Object.keys(gamedatas.players).length;

            // Setting up player boards
            document.documentElement.style.setProperty('--colsNb', gamedatas.gridSize[0]);
            document.documentElement.style.setProperty('--rowsNb', gamedatas.gridSize[1]);
            document.documentElement.style.setProperty('--playerCount', playerCount);
            document.documentElement.style.setProperty('--playerCountMinus1', playerCount - 1);

            for (let index = 1; index <= 5; index++) {
                if (i != playerCount) {
                    this.dontPreloadImage('board' + playerCount + 'P.png');
                }
            }

            for (var player_id in gamedatas.players) {
                var player = gamedatas.players[player_id];

                // TODO: Setting up players boards if needed
            }

            // TODO: Set up your game interface here, according to "gamedatas"

            this.inherited(arguments);
            this.updateAttractionCount();
            this.setBonusZIndex();
            // Setup game notifications to handle (see "setupNotifications" method below)
            this.setupNotifications();

            if (!this.isSpectator) {
                this.playerOrder = gamedatas.players[this.player_id].no;
                $(`bonus_breeding_time_${this.playerOrder}`).innerHTML = _('Bonus breeding');
            }
            dojo.query('.nyz_animal_action_zone, .nyz_birth_zone').forEach((node) => {
                this.updateTooltip(node.id);
            });

            debug('Ending game setup');
        },

        setupPlayer: function (playerId, playerInfo, gamedatas) {
            debug('player info ' + playerId, playerInfo);
            // move miniboards to the right
            var playerBoardDiv = dojo.byId('player_board_' + playerId);
            var order = gamedatas.players[playerId].no;
            dojo.place('miniboard_' + order, playerBoardDiv);

            this.setupPlayerOrderHints(playerId, gamedatas);
        },

        /** adds previous and next player color and name in a tooltip */
        setupPlayerOrderHints(playerId, gamedatas) {
            var nameDiv = this.queryFirst('#player_name_' + playerId + ' a');
            var playerIndex = gamedatas.playerorder.indexOf(parseInt(playerId)); //playerorder is a mixed types array
            if (playerIndex == -1) playerIndex = gamedatas.playerorder.indexOf(playerId.toString());

            var previousId =
                playerIndex - 1 < 0
                    ? gamedatas.playerorder[gamedatas.playerorder.length - 1]
                    : gamedatas.playerorder[playerIndex - 1];
            var nextId =
                playerIndex + 1 >= gamedatas.playerorder.length
                    ? gamedatas.playerorder[0]
                    : gamedatas.playerorder[playerIndex + 1];
            dojo.create(
                'div',
                {
                    class: 'playerOrderHelp',
                    title: gamedatas.players[previousId].name,
                    style: 'color:#' + gamedatas.players[previousId]['color'],
                    innerHTML: '&gt;',
                },
                nameDiv,
                'before'
            );
            dojo.create(
                'div',
                {
                    class: 'playerOrderHelp',
                    title: gamedatas.players[nextId].name,
                    style: 'color:#' + gamedatas.players[nextId]['color'],
                    innerHTML: '&gt;',
                },
                nameDiv,
                'after'
            );
        },

        setupScrollableMap: function () {
            // Scrollable area  for pieces
            this.scrollmap = new bgagame.extscrollmap();
            // Make map scrollable
            this.scrollmap.create($('map_container'), $('map_scrollable'), $('map_surface'), $('market'));
            this.scrollmap.only_x = true;
            this.scrollmap.setupOnScreenArrows(150);
            this.scrollmap.setPos(0, 0);
            document.addEventListener('blur', (e) => {
                // Cancel my drag and drop here
                this.scrollmap.onMouseUp(e);
            });
        },

        setupToken: function (token, tokenRec) {
            if (token.startsWith('patch')) {
                var tokenId = token; //tokens with several occurrences should be found by their type id
                if (token.split('_').length == 3) {
                    tokenId = token.substring(0, token.lastIndexOf('_'));
                }
                var mat = this.gamedatas.token_types[tokenId];

                //					var tokenInfo = this.getTokenDisplayInfo(token);
                //					$(token).title = this.format_string_recursive(
                //						tokenInfo.title.log,
                //						tokenInfo.title.args);

                var dx = Math.floor(Math.random() * CELL_WIDTH);
                var dy = Math.floor(Math.random() * CELL_WIDTH * (4 - mat.h));
                var rotateZ = Math.floor(Math.random() * 20 - 10);

                var tokenInfo = this.gamedatas.tokens[token];
                var location = tokenInfo.location;

                if (location == 'hand_' + this.player_id) {
                    dojo.setAttr(token, 'data-start-fence', 'true');
                }
                dojo.setAttr(token, {
                    'data-order': this.getTokenState(token),
                    'data-dx': dx,
                    'data-dy': dy,
                    'data-rz': rotateZ,
                });

                if (location.startsWith('action_zone') && mat.h > mat.w) {
                    //rotate to minimize board width needed
                    dojo.addClass(token, 'minimized');
                }
            } else if (token == 'token_neutral') {
                var dx = CELL_WIDTH;
                var dy = CELL_WIDTH * 2;
                var rotateZ = 0;
                dojo.setAttr(token, {
                    'data-order': this.getTokenState(token),
                    'data-dx': dx,
                    'data-dy': dy,
                    'data-rz': rotateZ,
                });
            } else {
                //debug('setupToken unknown', token);
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

            /*const animalZonesQuery = document.querySelectorAll('.nyz_animal_action_zone');
                for (const item of animalZonesQuery) {
                    item.addEventListener("click", event => this.onClickAnimalZone(event), false)
                }*/
            this.connectClass('nyz_animal_action_zone', 'onclick', 'onAnimalZone');
            const playerOrder = this.gamedatas.players[this.player_id].no;
            dojo.query(`.pboard_${playerOrder} .house`).connect('onclick', this, 'onHouse');

            //this.connectClass('token_neutral', 'onclick', 'onZoomPlus');
            this.notif_eofnet();

            // DEBUG BUTTON
            var parent = this.queryFirst('.debug_section');
            if (parent) {
                var butt = dojo.create('a', { class: 'bgabutton bgabutton_gray', innerHTML: 'Reload CSS' }, parent);
                dojo.connect(butt, 'onclick', () => reloadCss());
            }

            debug('enging token setup');
        },
        adjustScrollMap: function (duration) {
            debug('************adjustScrollMap');
            // we need to move market cursor to center on 3 pieces to select
            if (!$('thething')) return;
            var container = $('market').parentNode;
            if (!container && !this.scrollmap) return;
            $('thething').style.removeProperty('height');

            var cbox = dojo.contentBox('token_neutral');
            //debug('********cbox neutral', cbox);
            if (this.scrollmap) {
                let actionZone = $('token_neutral').parentNode.id;
                let zoneNumber = getPart(actionZone, 2);
                var cbox = dojo.contentBox($('token_neutral').parentNode);
                //debug('********cbox', $('token_neutral').parentNode.id, cbox);

                let mapWidth = this.scrollmap.container_div.getBoundingClientRect().width; //dojo.contentBox(this.scrollmap).l;
                //debug('mapX', mapWidth);
                if (zoneNumber <= 13) {
                    //upper line, need to see forward, but not too far
                    width = cbox.l;
                    // debug('*******first line width', width);
                    const lastZoneCBox = dojo.contentBox('action_zone_13');
                    //debug('action_zone_13.getBoundingClientRect()', lastZoneCBox);
                    width = Math.min(width, dojo.contentBox('action_zone_13').l + lastZoneCBox.w - mapWidth + 30);
                    //debug('*******capped width', width);
                    width = width * -1;
                } else {
                    //lower line, need to see backwards, but not too far
                    width = cbox.l + cbox.w - mapWidth;
                    //debug('*******lower line width', width);
                    width = width * -1;
                    width = Math.min(width, 0);
                    //debug('*******capped width', width);
                }
                setTimeout(() => {
                    this.scrollmap.onsurface_div.dataset.autoScroll = true; //for smooth scroll
                    this.scrollmap.setPos(width, 0);
                    setTimeout(() => {
                        this.scrollmap.onsurface_div.dataset.autoScroll = false;
                    }, 1000);
                }, 400);
            }
        },

        ///////////////////////////////////////////////////
        //// Game & client states

        // onEnteringState: this method is called each time we are entering into a new game state.
        //                  You can use this method to perform some user interface changes at this moment.
        //
        /** @Override */
        format_string_recursive: function (log, args) {
            try {
                //console.trace("format_string_recursive(" + log + ")", args);
                if (args.log_others !== undefined && this.player_id != args.player_id) {
                    log = args.log_others;
                }

                if (log && args && !args.processed) {
                    args.processed = true;

                    if (args.you) args.you = this.divYou(); // will replace ${you} with colored version
                    args.You = this.divYou(); // will replace ${You} with colored version

                    var keys = ['token_name', 'token_divs', 'token_names', 'token_div', 'token_div_count', 'animal'];
                    for (var i in keys) {
                        var key = keys[i];
                        // debug("checking " + key + " for " + log);
                        if (args[key] === undefined) continue;

                        if (key == 'token_divs') {
                            var list = args[key].split(',');
                            var res = '';
                            for (var l = 0; l < list.length; l++) {
                                res += this.getTokenDivForLogValue('token_div', list[l]);
                            }
                            res = res.trim();
                            if (res) args[key] = res;
                            continue;
                        }
                        if (key == 'token_names') {
                            var list = args[key].split(',');
                            var res = '';
                            for (var l = 0; l < list.length; l++) {
                                var name = this.getTokenDivForLogValue('token_name', list[l]);
                                res += name + ' ';
                            }
                            res = res.trim();
                            if (res) args[key] = res;
                            continue;
                        }
                        if (typeof args[key] == 'string') {
                            if (this.getTranslatable(key, args) != -1) {
                                continue;
                            }
                        }
                        var res = this.getTokenDivForLogValue(key, args[key]);
                        if (res) args[key] = res;
                    }
                }
            } catch (e) {
                console.error(log, args, 'Exception thrown', e.stack);
            }
            return this.inherited(arguments);
        },

        getTokenDivForLogValue: function (key, value) {
            // ... implement whatever html you want here
            //debugger;
            var token_id = value;
            if (token_id == null) {
                return '? ' + key;
            }
            if (key == 'token_div_count') {
                var node = this.divInlineTokenNode(value.args.token_div);
                node.innerHTML = value.args.mod;
                return node.outerHTML;
            }
            if (key == 'token_div') {
                var node = this.divInlineTokenNode(value, true);
                return node.outerHTML;
            }
            if (key.endsWith('name')) {
                var name = this.getTokenName(token_id);
                var div = "<span>'" + name + "'</span>";
                return div;
            }
            if (key == 'animal') {
                if (typeof value == 'string') {
                    return `<div class="animal ${value}" title="${value}"></div>`;
                }
            }
            return this.divInlineToken(token_id);
        },
        isPracticeMode: function () {
            return dojo.hasClass('ebd-body', 'practice_mode');
        },

        // state hooks
        onEnteringState: function (stateName, args) {
            this.inherited(arguments);

            /* document.querySelectorAll(`.tableau_${gameui.player_no} .pieces .animal`).forEach((item) => {
                item.addEventListener('click', (event) => this.onAnimal(event), false);
            });*/
        },

        onEnteringState_chooseFence(args) {
            if (args['bonusBreeding']) {
                if (this.isCurrentPlayerActive()) {
                    this.setDescriptionOnMyTurn(
                        _('Bonus breeding : ${you} can choose an enclosure that has not already breed this turn')
                    );
                } else {
                    this.setDescriptionOnOthersTurn(
                        _('Bonus breeding : ${actplayer} can choose an enclosure that has not already breed this turn')
                    );
                }
            }
        },

        onEnteringState_populateNewFence(args) {
            if (this.isCurrentPlayerActive()) {
                if (args.canDismiss) {
                    this.setDescriptionOnMyTurn(
                        _('${you} can place another animal on your new enclosure (from houses or other enclosures)')
                    );
                } else {
                    this.setDescriptionOnMyTurn(
                        _('${you} must place one animal on your new enclosure (from houses or other enclosures)')
                    );
                }
            } else {
                if (args.canDismiss) {
                    this.setDescriptionOnOthersTurn(_('${actplayer} can place another animal on the new enclosure'));
                } else {
                    this.setDescriptionOnOthersTurn(_('${actplayer} must place one animal on the new enclosure'));
                }
            }
        },

        processStateArgs(stateName, args) {
            //debug('before processStateArgs: ', args);
            return stateName == 'placeStartFences' ? args[this.player_id] : args;
        },

        onLeavingState: function (stateName) {
            this.inherited(arguments);
            if (!this.on_client_state) {
                dojo.query('.original').forEach((node) => {
                    if (node.id.endsWith('_temp')) {
                        dojo.destroy(node);
                    }
                });
            }

            this.removeClass('cannot_use');
            this.removeClass('active_slot');
            this.removeClass('practice_mode');
            this.removeClass('animal-target-image');
        },

        onUpdateActionButtons: function (stateName, args) {
            this.inherited(arguments);
        },
        onUpdateActionButtons_common: function (stateName, args, ret) {
            if (stateName == 'playerTurn' || stateName == 'placeStartFences') {
                gameui.addImageActionButton(
                    'practice',
                    _('Practice Mode'),
                    () => {
                        $('ebd-body').classList.add('practice_mode');
                        //this.onUpdateActionButtons_client_PickPatch(stateName == 'placeStartFences'?args[this.player_id]:args);
                        this.onUpdateActionButtons_client_PickPatch(args);
                    },
                    undefined,
                    _('In this mode you can place any enclosures to practice fitting')
                );
            }
        },

        onUpdateActionButtons_playerTurn: function (args) {
            this.clientStateArgs.action = 'place';

            if (args.hasOwnProperty('patches')) {
                var canBuy = Object.keys(args.patches);
                canBuy.forEach((id) => {
                    var canUse = args.patches[id].canUse;
                    if (canUse == false) dojo.addClass(id, 'cannot_use');
                    else dojo.addClass(id, 'active_slot');
                });
            }

            args.canGetAnimals.forEach((id) => {
                dojo.addClass(id, 'active_slot');
            });

            var pickcolor = 'blue';
            if (!args.canPatch) pickcolor = 'red';
            gameui.addImageActionButton(
                'b',
                _('Pick enclosure'),
                () => {
                    if (args.canPatch) this.setClientStateAction('client_PickPatch');
                    else this.showError(_('No legal moves'));
                },
                pickcolor
            );
            gameui.addImageActionButton(
                'a',
                _('Get animals'),
                () => {
                    if (args.canGetAnimals) this.setClientStateAction('client_GetAnimals');
                    else this.showError(_('No space to put those animals'));
                },
                'blue',
                _('Get one or two animals and place them')
            );
        },

        onUpdateActionButtons_populateNewFence: function (args) {
            this.clientStateArgs.action = 'placeAnimal';
            var possibleAnimals = Object.values(args.possibleAnimals);
            possibleAnimals.forEach((id) => {
                this.addActiveSlot(id, 'onAnimal');
            });

            var possibleTargets = Object.values(args.possibleTargets);
            possibleTargets.forEach((id) => {
                debug('args.canGetAnimals', id);
                dojo.addClass(id, 'active_slot');
                $(this.replaceGridSquareByHighlightSquare(id))?.classList.add('active_slot');
            });

            if (args.canDismiss) {
                gameui.addImageActionButton(
                    'c',
                    _('Dismiss'),
                    () => {
                        gameui.ajaxClientStateAction('dismiss');
                    },
                    'blue'
                );
            }
        },

        onUpdateActionButtons_placeAnimalFromHouse: function (args) {
            this.clientStateArgs.action = 'placeAnimalFromHouse';

            gameui.addActionButton(
                'yes',
                _('Yes'),
                () => {
                    //todo translate i18
                    this.ajaxClientStateAction();
                },
                null,
                null,
                'blue'
            );

            gameui.addActionButton(
                'c',
                _('No'),
                () => {
                    gameui.ajaxClientStateAction('dismiss');
                },
                null,
                null,
                'blue'
            );
        },

        onUpdateActionButtons_chooseFence: function (args) {
            gameui.clientStateArgs.action = 'chooseFences';
            gameui.clientStateArgs.squares = [];
            debug('squaresByFence', args);
            var squaresByFence = Object.entries(args['squares']);

            squaresByFence.forEach(([fence, squares]) => {
                squares.forEach((square) => {
                    dojo.addClass(square, 'active_slot');
                    $(this.replaceGridSquareByHighlightSquare(square))?.classList.add('active_slot');
                });
            });
            gameui.addImageActionButton(
                'breed',
                _('Confirm'),
                () => {
                    //check if selected fences matches possibles breedings
                    if (gameui.clientStateArgs.squares.length != args.possibleBreedings) {
                        this.confirmationDialog(
                            _('You did not select all the possible breedings, do you want to proceed ?'),
                            dojo.hitch(this, function () {
                                this.setClientStateAction('chooseFences');
                                gameui.clientStateArgs.squares = gameui.clientStateArgs.squares.join(' ');
                                gameui.ajaxClientStateAction();
                            })
                        );
                    } else {
                        this.setClientStateAction('chooseFences');
                        gameui.clientStateArgs.squares = gameui.clientStateArgs.squares.join(' ');
                        gameui.ajaxClientStateAction();
                    }
                },
                'blue'
            );

            gameui.addImageActionButton(
                'c',
                _('Dismiss'),
                () => {
                    this.setClientStateAction('chooseFences');
                    gameui.clientStateArgs.squares = '';
                    gameui.ajaxClientStateAction();
                },
                'red'
            );
        },

        onUpdateActionButtons_placeStartFences: function (args) {
            const playerOrder = this.gamedatas.players[this.player_id].no;
            const placedPatchesCount = this.queryIds(`.pieces_${playerOrder} .patch`).length;
            if (placedPatchesCount > 1) {
                //the first one is the filler
                gameui.addImageActionButton(
                    'c',
                    _('Reset'),
                    () => {
                        //todo remove draggable
                        gameui.ajaxClientStateAction('resetStartFences');
                    },
                    'red'
                );
            }
        },

        onUpdateActionButtons_client_GetAnimals: function (args) {
            this.setDescriptionOnMyTurn(
                _('Select a blue animal acquisition zone, then place them into houses or enclosures')
            );
            args.canGetAnimals.forEach((id) => {
                dojo.addClass(id, 'active_slot');
            });
        },
        onUpdateActionButtons_client_PickPatch: function (args) {
            this.onUpdateActionButtons_commonClientPickPatch(args);
        },

        onUpdateActionButtons_placeAttraction: function (args) {
            this.clientStateArgs.action = 'place';
            var canBuy = Object.keys(args.patches);
            canBuy.forEach((id) => {
                var canUse = args.patches[id].canUse;
                dojo.addClass(id, 'active_slot');
                if (canUse == false) dojo.addClass(id, 'cannot_use');
            });

            var pickcolor = 'blue';
            if (!args.canPatch) pickcolor = 'red';
            gameui.addImageActionButton(
                'b',
                _('Pick attraction'),
                () => {
                    if (args.canPatch) this.setClientStateAction('client_PickPatch');
                    else this.showError(_('No legal moves'));
                },
                pickcolor
            );

            gameui.addActionButton(
                'c',
                _('Dismiss'),
                () => {
                    gameui.ajaxClientStateAction('dismissAttraction');
                },
                null,
                null,
                'blue'
            );
        },

        updateAttractionCount() {
            dojo.query(`.group-counter`).forEach((g) => {
                g.innerHTML = g.parentNode.childElementCount - 1;
                if (g.innerHTML == 0) {
                    g.parentNode.style.display = 'none';
                } else {
                    g.parentNode.style.display = 'grid';
                }
            });
        },

        onUpdateActionButtons_commonClientPickPatch: function (args) {
            debug('Calling  onUpdateActionButtons_client_PickPatch', args);
            dojo.empty('generalactions');
            dojo.query('.done_control,.control-node').removeClass('active_slot');

            this.clientStateArgs.action = 'place';
            if (this.gamedatas.gamestate.name == 'placeStartFences') {
                this.clientStateArgs.action = 'placeStartFence';
            }
            //todo différencier bonus et patch
            debug('args.patches', args['patches']);
            var canBuy = Object.keys(args['patches'] ?? []);
            canBuy.forEach((id) => {
                var canUse = args.patches[id].canUse;
                if (canUse == false) dojo.addClass(id, 'cannot_use');
                else dojo.addClass(id, 'active_slot');
            });

            var sel = document.querySelector('.selected');
            var pick = document.querySelector('.pickTarget');
            var controls = false;
            if (pick) {
                if (this.isPracticeMode()) this.setDescriptionOnMyTurn(_('Press Cancel to exit practice mode'));
                else this.setDescriptionOnMyTurn(_('Press Confirm when ready or Cancel to start over'));
                //this.removeClass('active_slot');
                controls = true;
            } else if (sel) {
                this.setDescriptionOnMyTurn(_('Place the enclosure on your zoo. You can drag and drop'));
                controls = true;
                this.pm.updateActiveSquares();
            } else {
                this.setDescriptionOnMyTurn(_('Select an enclosure, then place it. You can drag and drop'));
            }
            if (this.isPracticeMode()) {
                this.setMainTitle(_('PRACTICE MODE:'), 'before');
                dojo.query('.market .patch').addClass('active_slot');
            }

            if (controls) {
                gameui.addImageActionButton('rotate_control_b', this.createDiv('rotate-image control-image'), (event) =>
                    this.pm.onClickPatchControl(event)
                );
                gameui.addImageActionButton(
                    'rotateR_control_b',
                    this.createDiv('rotate-image control-image flip-v'),
                    (event) => this.pm.onClickPatchControl(event)
                );
                gameui.addImageActionButton('flip_control_b', this.createDiv('mirror-image control-image'), (event) =>
                    this.pm.onClickPatchControl(event)
                );
                //gameui.addImageActionButton('flipH_control_b', this.createDiv('mirror-image control-image rotate-right'),
                //     (event) => this.pm.onClickPatchControl(event));
                //drop-zone
                document.querySelectorAll('.bgaimagebutton .control-image').forEach((item) => {
                    item.classList.add('drop-zone', 'control-node');
                    this.pm.addDropListeners(item, false);
                });
            }

            if (pick && !this.isPracticeMode()) {
                dojo.query('.done_control,.cancel_control,.control-node').addClass('active_slot');
                this.addDoneButton(_('Confirm'), 'onDone');
            }
            this.addCancelButton();
        },

        onUpdateActionButtons_placeAnimal: function (args) {
            this.onUpdateActionButtons_commonPlaceAnimal(args);
        },

        onUpdateActionButtons_keepAnimalFromFullFence: function (args) {
            this.clientStateArgs.action = 'keepAnimalFromFullFence';
            gameui.addActionButton(
                'yes',
                _('Yes'),
                () => {
                    //todo translate i18
                    this.ajaxClientStateAction();
                },
                null,
                null,
                'blue'
            );

            gameui.addActionButton(
                'c',
                _('No'),
                () => {
                    gameui.ajaxClientStateAction('dismiss');
                },
                null,
                null,
                'blue'
            );
        },

        onUpdateActionButtons_commonPlaceAnimal: function (args) {
            this.clientStateArgs.action = 'placeAnimal';
            var possibleAnimals = [];
            if (args.animalType1) possibleAnimals.push(args.animalType1);
            if (args.animalType2) possibleAnimals.push(args.animalType2);

            for (const anml of possibleAnimals) {
                var pickcolor = 'blue';
                if (!args.animals[anml].canPlace) pickcolor = 'red';

                gameui.addImageActionButton(
                    'place_animal_' + anml,
                    this.createDiv(anml + ' smallIcon'),
                    () => {
                        //todo translate i18

                        if (args.animals[anml].canPlace) {
                            this.setClientStateAction('client_PlaceAnimal');
                            this.clientStateArgs.animalType = anml;
                            this.setDescriptionOnMyTurn(_('Place the ${animalType} in a house or with his friends'), {
                                'animalType': anml,
                            });

                            args.animals[anml].possibleTargets.forEach((id) => {
                                dojo.addClass(id, 'active_slot');
                                $(this.replaceGridSquareByHighlightSquare(id))?.classList.add('active_slot');
                            });
                        } else this.showError(_('No legal location'));
                    },
                    pickcolor,
                    _('Place this animal now')
                );
            }

            var choosableAnimals = Object.keys(args['choosableAnimals'] ?? []);
            for (const anml of choosableAnimals) {
                var pickcolor = 'gray';
                debug('anml', anml);
                gameui.addImageActionButton(
                    'place_animal_' + anml,
                    this.createDiv(anml + ' smallIcon'),
                    () => {
                        this.setClientStateAction('client_PlaceAnimal');
                        this.clientStateArgs.animalType = anml;
                        this.setDescriptionOnMyTurn(_('Place the ${animalType} in a house or with his friends'), {
                            'animalType': anml,
                        });

                        args.choosableAnimals[anml].possibleTargets.forEach((id) => {
                            dojo.addClass(id, 'active_slot');
                            $(this.replaceGridSquareByHighlightSquare(id))?.classList.add('active_slot');
                        });
                    },
                    pickcolor,
                    _(
                        'If you take this animal, you’ll get only one animal instead of the two from your acquisition zone (the blue ones)'
                    )
                );
            }
            if (choosableAnimals?.length > 0) {
                this.setDescriptionOnMyTurn(_('Place 2 blue animals OR 1 white animal'), {});
            } else {
                this.setDescriptionOnMyTurn(_('${you} can place an animal'), {});
            }
            if (args.canDismiss) {
                gameui.addImageActionButton(
                    'c',
                    _('Dismiss'),
                    () => {
                        gameui.ajaxClientStateAction('dismiss');
                    },
                    'blue'
                );
            }
        },

        // debug state
        onUpdateActionButtons_playerGameEnd: function (args) {
            gameui.addActionButton('b', _('End'), () => gameui.ajaxClientStateAction('endGame'));
        },

        // UTILS
        setBonusZIndex: function () {
            for (let i = 0; i < 8; i++) {
                dojo.forEach(dojo.query(`#bonus-mask-${i} .patch`), function (el, j) {
                    el.style.zIndex = j;
                });
            }
        },

        replaceGridSquareByAnimalSquare: function (squareId) {
            return squareId.replace('square_', 'anml_square_');
        },

        replaceGridSquareByHighlightSquare: function (squareId) {
            let hSquare;
            if (squareId.startsWith('square_')) hSquare = squareId.replace('square_', 'highlight_square_');
            else if (squareId.startsWith('anml_square_'))
                hSquare = squareId.replace('anml_square_', 'highlight_square_');
            return hSquare;
        },

        cancelLocalStateEffects: function () {
            //if (this.curstate == 'client_PickPatch') {
            this.pm.cancelPickPatch();
            this.pm.endPickPatch();
            $('overall-content').classList.remove('placingFence');
            // }
            this.inherited(arguments);
        },
        ajaxActionResultCallback: function (action, args, result) {
            this.inherited(arguments);
            debug('ajax callback');
            if (action == 'place' || action == 'placeStartFence') this.pm.cancelPickPatch();
            if (action == 'placeStartFence') {
                this.pm.endPickPatch();
                this.onLeavingState(this.gamedatas.state);
            }
        },
        onPlaceToken: function (tokenId) {
            var token = $(tokenId);
            if (!token) return; // destroyed
            this.updateAttractionCount();
        },
        onDone: function () {
            $('overall-content').classList.remove('placingFence');
            var token = gameui.clientStateArgs.token;
            var id = gameui.clientStateArgs.dropTarget;
            if (!gameui.isActiveSlot(id)) {
                if (!this.practiceMode) gameui.showError(_('Illegal fence location'));
                return;
            }
            dojo.destroy(token + '_temp');
            gameui.removeClass('original');
            gameui.removeClass('active_slot');
            this.pm.selectedNode = null;

            gameui.clientStateArgs.rotateZ = this.pm.normalizedRotateAngle(gameui.clientStateArgs.rotateZ);
            gameui.clientStateArgs.rotateY = this.pm.normalizedRotateAngle(gameui.clientStateArgs.rotateY);
            var state = this.pm.getRotateState();
            gameui.placeTokenLocal(gameui.clientStateArgs.token, gameui.clientStateArgs.dropTarget, state, {
                noa: true,
            });

            if ($(token).draggable) {
                //patch just placed
                $(token).draggable = false;
            }

            this.ajaxClientStateAction();
        },

        getPlaceRedirect: function (token, tokenInfo) {
            var location = tokenInfo.location;
            var result = {
                location: location,
                inlinecoords: false,
            };

            if (location.startsWith('hand')) {
                var state = parseInt(tokenInfo.state);
                debug('state', state);
                //result.position = 'absolute';
                var tokenNode = $(token);
                if (!tokenNode) return result; // ???
                dojo.style(tokenNode, 'order', state);
                return result;
            }
            if (location.startsWith('market')) {
                var state = parseInt(tokenInfo.state);
                result.position = 'absolute';
                var tokenNode = $(token);
                if (!tokenNode) return result; // ???
                dojo.setAttr(tokenNode, 'data-order', state);

                var dx = parseInt(dojo.getAttr(tokenNode, 'data-dx'));
                var dy = parseInt(dojo.getAttr(tokenNode, 'data-dy'));
                var rotateZ = parseInt(dojo.getAttr(tokenNode, 'data-rz'));
                if (rotateZ) {
                    var rule = 'rotateY(0deg) rotateZ(' + rotateZ + 'deg)';
                    tokenNode.style.transform = rule;
                }

                var x = dojo.getAttr(tokenNode, 'data-x');
                var y = dojo.getAttr(tokenNode, 'data-y');
                var mwidth = 0;
                var mheight = 0;
                if (x !== undefined) {
                    result.x = x;
                } else {
                    result.x = dx + mwidth;
                }
                if (y !== undefined) {
                    result.y = y;
                } else {
                    result.y = dy + mheight;
                }

                return result;
            }
            if (location.startsWith('square')) {
                result.inlinecoords = true;

                var top = getIntPart(location, 2) * CELL_WIDTH;
                var left = getIntPart(location, 3) * CELL_WIDTH;

                result.x = left;
                result.y = top;
                result.location = 'pieces_' + getPart(location, 1);
                if ($(token)) $(token).style.removeProperty('transform');
            }
            if (location.startsWith('bonus_market')) {
                //redirects to the sub group corresponding to the mask
                const mask = this.getRulesFor(token, 'mask');
                result.location = this.queryFirstId(`.bonus_market [data-mask-group="${mask}"]`);
                return result;
            }
            /* if (location.startsWith('action_zone')) {
                     result.inlinecoords = true;
                     var locbox = dojo.contentBox(location);
                     var tokenbox = dojo.contentBox(token);
                     var width = cbox.w - 40;
                     var height = cbox.h - 40;
                     result.x = locbox.w - tokenbox.w/2;
                     result.y = locbox.h - tokenbox.h/2;
                     if (!$(token))
                         this.createToken(token, tokenInfo, location);
                     //dojo.attr(token, 'data-pos', getIntPart(token, 1));
                     return result;
                 }*/

            return result;
        },
        ///////////////////////////////////////////////////
        //// Utility methods

        /*
            
                Here, you can defines some utility methods that you can use everywhere in your javascript
                script.
            
            */
        onAnimalZone: function (event) {
            dojo.stopEvent(event);
            var id = event.currentTarget.id;
            gameui.clientStateArgs.action = 'getAnimals';
            gameui.clientStateArgs.actionZone = id;
            if (!gameui.isActiveSlot(id)) {
                return;
            }

            gameui.removeClass('original');
            gameui.removeClass('active_slot');

            debug('onAnimalZone', gameui.clientStateArgs);
            gameui.ajaxClientStateAction();
        },

        onHouse: function (event) {
            dojo.stopEvent(event);
            var id = event.currentTarget.id;
            debug('onHouse', id);

            if (gameui.curstate === 'client_PlaceAnimal') {
                if (!gameui.isActiveSlot(id)) {
                    return;
                }
                gameui.clientStateArgs.action = 'placeAnimal';
                gameui.clientStateArgs.to = id;
                gameui.removeClass('original');
                gameui.removeClass('active_slot');

                gameui.ajaxClientStateAction();
            } else if (gameui.curstate === 'populateNewFence') {
                if (!gameui.childIsActiveSlot(id)) {
                    return;
                }
                gameui.clientStateArgs.from = $(id).firstElementChild.id;
            }
            debug('onHouse', gameui.clientStateArgs);
        },

        onAnimal: function (event) {
            var id = event.currentTarget.id;

            switch (gameui.curstate) {
                case 'populateNewFence':
                    dojo.stopEvent(event);
                    $(id).classList.toggle('selected');
                    gameui.clientStateArgs.from = id;
                    break;

                default:
                    break;
            }
            debug('onAnimal', gameui.clientStateArgs);
        },

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
                debug( 'onMyMethodToCall1' );
                
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
            this.inherited(arguments);
            // TODO: here, associate your game notifications with local methods

            // Example 1: standard notification handling
            // dojo.subscribe( 'cardPlayed', this, "notif_cardPlayed" );

            // Example 2: standard notification handling + tell the user interface to wait
            //            during 3 seconds after calling the method in order to let the players
            //            see what is happening in the game.
            // dojo.subscribe( 'cardPlayed', this, "notif_cardPlayed" );
            // this.notifqueue.setSynchronous( 'cardPlayed', 3000 );
            //
            var _this = this;
            var notifs = [
                ['breedingTime', 1000],
                ['fenceFull', 1000],
                ['placeStartFenceArgs', 1],
                ['eofnet', 1],
            ];
            notifs.forEach(function (notif) {
                dojo.subscribe(notif[0], _this, 'notif_' + notif[0]);
                _this.notifqueue.setSynchronous(notif[0], notif[1]);
            });
        },

        // TODO: from this point and below, you can write your game notifications handling methods

        /*
            Example:
            
            notif_cardPlayed: function( notif )
            {
                debug( 'notif_cardPlayed' );
                debug( notif );
                
                // Note: notif.args contains the arguments specified during you "notifyAllPlayers" / "notifyPlayer" PHP call
                
                // TODO: play the card in the user interface.
            },    
            
            */
        notif_eofnet: function (notif) {
            this.adjustScrollMap();
        },

        notif_breedingTime(notif) {
            if (this.isReadOnly()) return;
            let notifDiv = $('breeding_time_' + this.playerOrder);
            const bonus = notif.args.bonus;
            let classes = ['animated'];
            let animal = '';
            if (bonus) {
                classes.push('bonus');
                notifDiv = $('bonus_breeding_time_' + this.playerOrder);
            } else {
                animal = notif.args.animalType;
                classes.push('notif-' + animal);
                if (Object.values(notif.args.cantBreed).indexOf(this.player_id + '') !== -1) {
                    classes.push('disabled');
                }
            }
            notifDiv.classList.add(...classes);
            setTimeout(() => notifDiv.classList.remove('animated', 'disabled', 'bonus', 'notif-' + animal), 1000);
        },

        notif_fenceFull(notif) {
            debug('notif_fenceFull', notif);
            $(notif.args.fence).classList.toggle('animated', !notif.args.resolved);
        },

        notif_placeStartFenceArgs(notif) {
            this.onEnteringState('placeStartFences', notif.args); //updates possible moves
        },
    });
});
