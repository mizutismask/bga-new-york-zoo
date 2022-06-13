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

<div id="central" class="central">

		<!-- BEGIN player_board -->
		
		<div id="tableau_{COLOR}" data-title="{PLAYER_NAME}" class="tableau tableau_{COLOR} {OWN} player_count_{PLAYER_COUNT} player_order_{PLAYER_NO}">
		
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

<div id="limbo" class="limbo">
		<div id="rotate_control_template" class="rotate-image control-image"></div>
		<div id="flip_control_template" class="mirror-image control-image"></div>
		<div id="done_control_template" class="done-image control-image"></div>
		<div id="cancel_control_template" class="cancel-image control-image"></div>

	</div>
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
