<h1>Import Yoast SEO CSV</h1>

<p>
	Before uploading your CSV please make sure it follows the same exact structure as 
	<a href="<?php echo plugin_dir_url(sprintf('%s/../assets/example.csv', __DIR__)) . 'example.csv' ?>">this example</a> CSV.
</p>

<form method="post" action="?page=yoast-csv-import" enctype="multipart/form-data">
	<label>Select CSV to upload</label><br />
	<input type="file" name="<?php echo YCIAdmin::FNAME ?>"><br />
	<button type="submit">Upload</button>
</form>


<?php if (!empty($log)): ?>
	<h2>CSV Import Progress:</h2>
	<ul id="log">
		<?php foreach($log as $line): ?>
			<li><?php echo $line ?></li>
		<?php endforeach; ?>
	</ul>
<?php endif; ?>