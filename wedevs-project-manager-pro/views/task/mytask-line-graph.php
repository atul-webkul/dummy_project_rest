<canvas id="chart-details"></canvas>

<script>
jQuery(function($) {



// For Line chart

var lineData = {
		    labels: [<?php echo $str_date ?>],
		    datasets: [
		        {
		            label: "<?php _e('Activity', 'cpm-pro') ; ?>",
		            fillColor: "rgba(97, 189, 79, 0.5)",
		            strokeColor: "#61BD4F",
		            pointColor: "#61BD4F",
		            pointStrokeColor: "#61BD4F",
		            pointHighlightFill: "#61BD4F",
		            pointHighlightStroke: "rgba(151,187,205,1)",
		            scaleLabel: "Test <%=value%>",
		            data: [<?php echo $str_activity ?>]
		        },
		        {
		            label: "<?php _e('Assign Task', 'cpm-pro') ?>",
		            fillColor: "rgba(89, 3, 64, 0.5)",
		            strokeColor: "#590340",
		            pointColor: "#590340",
		            pointStrokeColor: "#590340",
		            pointHighlightFill: "#590340",
		            pointHighlightStroke: "rgba(151,187,205,1)",
		            data: [<?php echo $str_task ?>]
		        },
		        {
		            label: "<?php _e('Complete Task', 'cpm-pro') ?>",
		            fillColor: "rgba(0, 144, 217, 0.5)",
		            strokeColor: "#0090D9",
		            pointColor: "#0090D9",
		            pointStrokeColor: "#0090D9",
		            pointHighlightFill: "#0090D9",
		            pointHighlightStroke: "rgba(151,187,205,1)",
		            data: [<?php echo $str_ctask ?>]
		        }
		    ]
		};


		Chart.defaults.global.responsive = true;
		var ctxl = $("#chart-details").get(0).getContext("2d");
                    ctxl.canvas.height = jQuery(".cpm-mytask-chart-overview").height()-102;

		// This will get the first returned node in the jQuery collection.
		var cpmChart = new Chart(ctxl).Line(lineData, {
			pointDotRadius : 8,
			animationSteps: 60,
			tooltipTemplate: "<%=label%>:<%= value %>",
			animationEasing: "easeOutQuart",
                        responsive: true,
                        maintainAspectRatio: false

		});



});
</script>
