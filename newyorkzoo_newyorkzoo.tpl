{OVERALL_GAME_HEADER}

<audio id="audiosrc_flamingo" src="{GAMETHEMEURL}/img/flamingo.mp3" preload="none"></audio>
<audio id="audiosrc_fox" src="{GAMETHEMEURL}/img/fox.mp3" preload="none"></audio>
<audio id="audiosrc_kangaroo" src="{GAMETHEMEURL}/img/kangaroo.mp3" preload="none"></audio>
<audio id="audiosrc_meerkat" src="{GAMETHEMEURL}/img/meerkat.mp3" preload="none"></audio>
<audio id="audiosrc_penguin" src="{GAMETHEMEURL}/img/penguin.mp3" preload="none"></audio>

<audio id="audiosrc_o_flamingo" src="{GAMETHEMEURL}/img/flamingo.ogg" preload="none"></audio>
<audio id="audiosrc_o_fox" src="{GAMETHEMEURL}/img/fox.ogg" preload="none"></audio>
<audio id="audiosrc_o_kangaroo" src="{GAMETHEMEURL}/img/kangaroo.ogg" preload="none"></audio>
<audio id="audiosrc_o_meerkat" src="{GAMETHEMEURL}/img/meerkat.ogg" preload="none"></audio>
<audio id="audiosrc_o_penguin" src="{GAMETHEMEURL}/img/penguin.ogg" preload="none"></audio>

<!-- 
--------
-- BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
-- NewYorkZoo implementation : © Séverine Kamycki severinek@gmail.com
-- 
-- This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
-- See http://en.boardgamearena.com/#!doc/Studio for more information.
-------

    newyorkzoo_newyorkzoo.tpl
    
    This is the HTML template of your game.
    
    Everything you are writing in this file will be displayed in the HTML page of your game user interface,
    in the "main game zone" of the screen.
    
    You can use in this template:
    _ variables, with the format {MY_VARIABLE_ELEMENT}.
    _ HTML block, with the BEGIN/END format
    
    See your "view" PHP file to check how to set variables and control blocks
    
    Please REMOVE this comment before publishing your game on BGA
-->
<div id="thething" class="thething">

<div id="map_container" class="map_container">
	<div id="map_scrollable" class="map_scrollable map_layer">
	</div>
	<div id="map_surface" class="map_surface map_layer"></div>
	<div id="market" class="market map_scrollable_oversurface map_layer">
		<div class="actionStripWrapper">
			<div class="actionStrip">

				<!-- BEGIN actionStripZone -->
				<div id="{ID}" class="nyz_action_zone {ANIMAL_ZONE} {LOWER_HALF}" style="left: {X}%; top: {Y}%; width: {WIDTH}%; height: {HEIGHT}%;">
					<div id="pile_{ID}_counter" class="pile-counter">0</div>
				</div>
				<!-- END actionStripZone -->

				<!-- BEGIN actionStripZoneSolo -->
				<div id="{ID}" class="nyz_action_zone {ANIMAL_ZONE}" style="left: {X}%; top: {Y}%; width: {WIDTH}%; height: {HEIGHT}%;">
					<div id="pile_{ID}_counter" class="pile-counter">0</div>
					<div class="soloTokenNeeded solo-token hidden"></div>
					<div class="soloTokenFree solo-token hidden">&olcross;</div>
				</div>
				<!-- END actionStripZoneSolo -->

				<!-- BEGIN birthZone -->
				<div id="{ID}" class="nyz_birth_zone" style="left: {X}%; top: {Y}%; width: {WIDTH}%; height: {HEIGHT}%;"></div>
				<!-- END birthZone -->

				<!-- BEGIN patch -->
				<div id="{PATCH_ID}" class="patch flipper patch_{NUM} {PATCH_CLASSES}">
					<div class="target-image target-spot"></div>
					<div class="patch-face patch-face-{NUM} shape-patch-{NUM} {PATCH_FACE_CLASSES}"></div>
					<svg class="patch-outline" draggable="false" >
						<polygon class="patch-outline-poligon"
							points="{POL_POINTS}"></polygon>
					</svg>
				</div>
				<!-- END patch -->

				<div id="token_neutral" class="token_neutral"></div>
			</div>
		</div>
	</div>
	<div class="moveleft"></div>
	<div class="moveright"></div>
</div>

<!-- BEGIN handMarket -->
<div id="hand_{PLAYER_ID}" class="hand_market market {OTHER_CLASSES}"></div>
<!-- END handMarket -->

<div id="central" class="central">

		<!-- BEGIN player_board -->
		<div id="miniboard_{ORDER}" class="miniboard">
		    <div class="mini_icons">
		        <div id="empties_{ORDER}_div" class="mini_board_item">
		             <div id="empties_{ORDER}_counter" class="empties_counter mini_counter">0</div>
		             <div id="empties_{ORDER}_icon" class="empty_icon micon"></div>
		        </div>
				<!-- BEGIN solo_counters -->
				<div id="rounds_completed_div" class="mini_board_item hidden">
    				<div id="rounds_completed_counter" class="mini_counter">0</div>
		            <div id="rounds_completed_icon" class="fa6 fa-regular fa6-arrow-rotate-right"></div>
				</div>
    			<!-- END solo_counters -->
		    </div>
		</div>

		<div id="tableau_{ORDER}" data-title="{PLAYER_NAME}" class="tableau tableau_{ORDER} {OWN} player_count_{PLAYER_COUNT} player_order_{ORDER} {SOLO_BOARD}">
			<div id="pboard_{ORDER}" class="pboard pboard_{ORDER}">
			
				<!-- BEGIN board_help -->
				<div id="help1players{PLAYER_COUNT}" class="css-icon nyz-help-1 nyz-help">?</div>
				<div id="help2players{PLAYER_COUNT}" class="css-icon nyz-help-2 nyz-help">?</div>
				<div id="help3players{PLAYER_COUNT}" class="css-icon nyz-help-3 nyz-help">?</div>
				<!-- END board_help -->
			
				<div id="squares_{ORDER}" class="squares squares_{ORDER}">
    <!-- BEGIN square -->
    				<div id="square_{ORDER}_{Y}_{X}" class="square {CLASSES}" style="left: {LEFT}px; top: {TOP}px;"></div>
    <!-- END square -->
				</div>
				<div id="pieces_{ORDER}" class="pieces pieces_{ORDER}"></div>
				<div id="animals_layer_{ORDER}" class="animals-layer animals-layer_{ORDER} squares">
					<!-- BEGIN anml_square -->
    				<div id="anml_square_{ORDER}_{Y}_{X}" class="anml-square {CLASSES}" style="left: {LEFT}px; top: {TOP}px;"></div>
    				<!-- END anml_square -->
				</div>
				<div id="highlight_layer_{ORDER}" class="highlight-layer highlight-layer_{ORDER} squares">
					<!-- BEGIN highlight_square -->
    				<div id="highlight_square_{ORDER}_{Y}_{X}" class="highlight-square {CLASSES}" style="left: {LEFT}px; top: {TOP}px;"></div>
    				<!-- END highlight_square -->
				</div>
				<div class="board_houses">
					<!-- BEGIN house -->
					<div id="house_{PLAYER_ORDER}_{HOUSE_INDEX}" class="house {CLASSES}"></div>
					<!-- END house -->
				</div>
			</div>
			<span class="player_name">{PLAYER_NAME}</span>
			<div id="breeding_time_{ORDER}_wrapper" class="breeding_time_wrapper">
				<div id="breeding_time_{ORDER}" class="breeding-notif breeding_time_{ORDER}"></div>
				<div id="bonus_breeding_time_{ORDER}" class="breeding-notif bonus"></div>
			</div>
		</div>

		<!-- END player_board -->
        </div>
</div>

<div id="circle_market" class="circle_market"></div>

<div id="bonus_market" class="bonus_market">
	<!-- BEGIN bonus-mask -->
	<div id="bonus-mask-{COUNTER}" class="bonus-mask-group" data-mask-group="{MASK}">
		<div class="group-counter"></div>
	</div>
	<!-- END bonus-mask -->
</div>
<div id="limbo" class="limbo">
	<div id="rotate_control_template" class="rotate-image control-image"></div>
	<div id="flip_control_template" class="mirror-image control-image"></div>
	<div id="done_control_template" class="done-image control-image"></div>
	<div id="cancel_control_template" class="cancel-image control-image"></div>
	
	<div id="solo_token_0" class="solo-token-0 solo-token"></div>
	<div id="solo_token_1" class="solo-token-1 solo-token"></div>
	<div id="solo_token_2" class="solo-token-2 solo-token"></div>
	<div id="solo_token_3" class="solo-token-3 solo-token"></div>
	<div id="solo_token_4" class="solo-token-4 solo-token"></div>
</div>

<style>
		<!-- BEGIN patchcss -->
		.shape-patch-{NUM} {
			-webkit-clip-path: polygon({CLIP_POINTS});
			clip-path: polygon({CLIP_POINTS});
			width: {W}px;
			height: {H}px;
		}
		<!-- END patchcss -->
</style>

<script type="text/javascript">

// Javascript HTML templates

/*
// Example:
var jstpl_some_game_item='<div class="my_game_item" id="my_game_item_${MY_ITEM_ID}"></div>';

*/

</script>  

{OVERALL_GAME_FOOTER}
