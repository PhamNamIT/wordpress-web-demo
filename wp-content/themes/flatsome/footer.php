<?php
/**
 * The template for displaying the footer.
 *
 * @package flatsome
 */

global $flatsome_opt;
?>

<script>
	var Arr = document.querySelectorAll(".none-hover");
	for (let item of Arr) {
		item.classList.remove("has-hover");
	}
	var Arr = document.querySelectorAll(".none-hover .has-hover");
	for (let item of Arr) {
		item.classList.remove("has-hover");
	}
	
	for(var i=1; i<4; i++) {
		var id = document.querySelector(".item-" + i).id;
		document.querySelector(".item-" + i).id = "item-" + i;
	}

</script>

</main>

<footer id="footer" class="footer-wrapper">

	<?php do_action('flatsome_footer'); ?>

</footer>

</div>

<?php wp_footer(); ?>

</body>
</html>
