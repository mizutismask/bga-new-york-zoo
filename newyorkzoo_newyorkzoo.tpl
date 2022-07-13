{OVERALL_GAME_HEADER}

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


This is your game interface. You can edit this HTML in your ".tpl" file.
<div id="thething" class="thething">
<div id="test" class="test">
<!-- BEGIN patchTest -->
				
					<div class="patch-face patch-face-{NUM} shape-patch-{NUM}"></div>
				
				<!-- END patchTest -->
</div>
<div id="map_container" class="map_container">
	<div id="map_scrollable" class="map_scrollable map_layer">
		<div class="actionStripWrapper">
			<div class="actionStrip">
				<!-- BEGIN actionStripZone -->
				<div id="{ID}" class="nyz_action_zone" style="left: {X}%; top: {Y}%; width: {WIDTH}%; height: {HEIGHT}%;"></div>
				<!-- END actionStripZone -->
				<!-- BEGIN patch -->
				<div id="{PATCH_ID}" class="patch flipper patch_{NUM}">
					<div class="target-image target-spot"></div>
					<div class="patch-face patch-face-{NUM} shape-patch-{NUM}"></div>
					<svg class="patch-outline" draggable="false" >
						<polygon class="patch-//outline-poligon"
							points="{POL_POINTS}"></polygon>
					</svg>
				</div>
				<!-- END patch -->
				<div id="token_neutral" class="token_neutral"></div>
			</div>
		</div>
	</div>
	<div id="map_surface" class="map_surface map_layer"></div>
	<div id="market" class="market map_scrollable_oversurface map_layer"></div>
	<div class="moveleft"></div>
	<div class="moveright"></div>
</div>

<div id="central" class="central">

		
		<!-- BEGIN player_board -->
		
		<div id="tableau_{COLOR}" data-title="{PLAYER_NAME}" class="tableau tableau_{COLOR} {OWN} player_count_{PLAYER_COUNT} player_order_{PLAYER_NO}">
				<!--<div class="board_houses"> -->
				<!-- BEGIN house -->
					<div id="house_{PLAYER_ID}_{HOUSE_INDEX}" class="house {CLASSES}"></div>
				<!-- END house -->
			<!--</div> -->
			<div id="pboard_{COLOR}" class="pboard pboard_{COLOR}">
				<div id="squares_{COLOR}" class="squares squares_{COLOR}">
    <!-- BEGIN square -->
    				<div id="square_{COLOR}_{Y}_{X}" class="square {CLASSES}" style="left: {LEFT}px; top: {TOP}px;"></div>
    <!-- END square -->
				</div>
				<div id="pieces_{COLOR}" class="pieces pieces_{COLOR}"></div>
			</div>
		</div>

		<!-- END player_board -->
        </div>

	
</div>
<div id="circle_market" class="circle_market"></div>
<div id="limbo" class="limbo">
	<div id="rotate_control_template" class="rotate-image control-image"></div>
	<div id="flip_control_template" class="mirror-image control-image"></div>
	<div id="done_control_template" class="done-image control-image"></div>
	<div id="cancel_control_template" class="cancel-image control-image"></div>
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
