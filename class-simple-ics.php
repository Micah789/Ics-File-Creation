<?php 
if (!defined('ABSPATH')) exit; // Exit if accessed directly

/**
 * Create ics file to add event to iCalendar
 * 
 * Building upon function https://gist.github.com/jakebellacera/635416/3c81643cc236a5efdf535fcbf3f876eaaa6c4787
 * 
 * @package SimpleICS
 * @author  Micah Kwaka
 * @since 0.3
 */
class SimpleICS {

  // define properties
  private $title;
  private $description;
  private $start_date;
	private $date_stamp;
  private $location;
  private $url;

  // constuctor
  function __construct(int $post_id) {
    $this->title = get_the_title($post_id);
    $this->description = show_max_char_length(get_post_content($post_id), 75);
    $this->location = get_field('location', $post_id);
    $this->start_date = get_field('event_date', $post_id);
    $this->url = get_permalink($post_id);
    $this->date_stamp = time();
  }

  /**
   * Convert times to iCalendar format. They require a block for yyyymmdd and then another block
   * for the time, which is in hhiiss. Both of those blocks are separated by a "T". The Z is
   * declared at the end for UTC time, but shouldn't be included in the date conversion.
   * @param mixed  $time date of event.
   * @return string $date
   */
  public function dateToCal($time) {
    $unixtimestamp = strtotime($time);
    return date('Ymd', $unixtimestamp); 
  }

  /**
   * Build the ics file
   * @return string $href
   */
  public function getHref() {
    $location = $this->location;

    // Prepare array for ics file
    $url = [
      'BEGIN:VCALENDAR',
      'VERSION:2.0',
      'BEGIN:VEVENT',
      'UID:'.md5($this->title),
      'SUMMARY:' . addslashes($this->title),
      'DTSTAMP:' . $this->dateToCal($this->date_stamp)
    ];

    $url[] = 'DTSTART:'.$this->dateToCal($this->start_date);
    $url[] = 'DURATION:P1D';

    if($this->url) {
      $url[] = 'URL;VALUE=URI:'.$this->url;
    }

    if($location) {
      $url[] = 'LOCATION:'.$this->escapeString($location['city']);
    }

    if($this->description) {
      $url[] = 'DESCRIPTION:'.$this->escapeString($this->description);
    }

    $url[] = 'END:VEVENT';
    $url[] = 'END:VCALENDAR';

    $redirectLink = implode("\r\n", $url);
    $href = $this->buildHref($redirectLink);

    if(!empty($href)) {
      return $href;
    }
  }

  /**
   * Build href with encoding
   * @param string  $redirectLink
   * @return string $href
   */
  public function buildHref(string $redirectLink): string {
    return 'data:text/calendar;charset=utf8;base64,'.base64_encode($redirectLink);
  }

  /** @see https://tools.ietf.org/html/rfc5545.html#section-3.3.11 */
  protected function escapeString(string $field): string {
    return addcslashes($field, "\r\n,;");
  }
}