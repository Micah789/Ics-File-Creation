# Simple ICS File Creation

* Languages: PHP mainly WordPress can be change to used Pure PHP

### Frontend PHP
```php
<?php include 'class-simple-ics.php'; ?>
<?php if (class_exists('SimpleICS')) : ?>
  <a download="<?= get_the_title(); ?>.ics" href="<?= (new SimpleICS(get_the_ID()))->getHref()?>">Add To Apple Calendar</a>  
<?php endif; ?>
```
