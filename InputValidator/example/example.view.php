<!-- Edit this form to represent a table from your database -->
<!-- Names of inputs must match the field's name in its respective table -->
<form method="post">

	<label for="vin_number">Vin Number</label>
	<input type="text" name="vin_number">

	<label for="manufacture_year">Manufacture Year</label>
	<input type="text" name="manufacture_year">

	<label for="make">Make</label>
	<input type="text" name="make">

	<label for="mileage">Mileage</label>
	<input type="text" name="mileage">

	<label for="created_date_time">Date Added</label>
	<input type="text" name="created_date_time">

	<input type="submit" value="Validate">

</form>

<?php if (isset($success_message)) : ?>

	<p style="color:green"><?=$success_message?></p>

<?php endif; ?>


<?php if ($validator->error) : ?>
	
	<?php foreach ($validator->error as $error) : ?>
	
		<p style="color:red"><?=$error[0]?></p>

	<?php endforeach; ?>

<?php endif; ?>
