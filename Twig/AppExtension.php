<?Php
namespace Adsign\FileManagerBundle\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Adsign\FileManagerBundle\DependencyInjection\jDateTime;

class AppExtension extends AbstractExtension
{




    public function getFilters()
    {
        return array(
            new TwigFilter('userid', array($this, 'UserIdFilter')),
            new TwigFilter('persianDateTime', array($this, 'pDateTimeFilter')),
            new TwigFilter('persianDate', array($this, 'pDateFilter')),
            new TwigFilter('persianDate2', array($this, 'pDateFilter2')),
            new TwigFilter('persianStringDate2', array($this, 'pDateFilterString2')),
            new TwigFilter('persianDateYear2', array($this, 'pDateFilterYear2')),
            new TwigFilter('persianDateMounth2', array($this, 'pDateFilterMounth2')),
            new TwigFilter('removefirstcharacter', array($this, 'removefirstcharacter')),
            new TwigFilter('persianMonthDay', array($this, 'pMonthDayFilter')),
            new TwigFilter('persianMonthDay2', array($this, 'pMonthDay2Filter')),
            new TwigFilter('persianWeekDay', array($this, 'pWeekDay')),
            new TwigFilter('leftTime', array($this, 'agoFilter')),
            new TwigFilter('getImageRoute', array($this, 'getMediaFileRoute')),
            new TwigFilter('getImageThumbRoute', array($this, 'getMediaThumbRoute')),
            new TwigFilter('getWallpaperImageRoute', array($this, 'getWallpaperFileRoute')),
        );
    }

    public function UserIdFilter($userId,$user)
    {
//        dump($userId);
//        die;
        $today = new \DateTime;
        $userId = base64_encode($userId + 1733 + $user + $today->format('dmY'));
        return $userId;
    }

    public function pDateTimeFilter($date)
    {
        if(!$date) return null;
		date_default_timezone_set('Asia/Tehran');
        //$jdf = new jdf();

		$jdate = new jDateTime();
		$gDate = $date->format('Y-m-d');
		$gDate = explode('-',$gDate);

        //return $jdf->jdate('Y/m/d',$date->getTimestamp(), null,$time_zone='Asia/Tehran',$tr_num='fa') . ' ساعت '.   $this->tr_num2($date->format('H:i'), 'fa');
        return $this->tr_num2($date->format('H:i'), 'fa').' '.$this->tr_num2($jdate->toJalali2($gDate[0],$gDate[1],$gDate[2]), 'fa');
    }

    public function tr_num2($str,$mod='en',$mf='٫'){
        $num_a=array('0','1','2','3','4','5','6','7','8','9','.');
        $key_a=array('۰','۱','۲','۳','۴','۵','۶','۷','۸','۹',$mf);
        return($mod=='fa')?str_replace($num_a,$key_a,$str):str_replace($key_a,$num_a,$str);
    }

    public function pDateFilter($date)
    {
        date_default_timezone_set('Asia/Tehran');
        //$jdf = new jdf();

        $jdate = new jDateTime();
        $gDate = $date->format('Y-m-d');
        $gDate = explode('-',$gDate);

        return $jdate->toJalali2($gDate[0],$gDate[1],$gDate[2]);
    }

    public function agoFilter($date){
        $interval = date_create('now')->diff( $date );
        $diff = 10;
        $suffix = ( $interval->invert ? ' قبل' : '' );
        if ( $v = $interval->y >= 1 ) $diff =  $interval->y * 365 ;
        if ( $v = $interval->m >= 1 ) $diff = $diff + $interval->m * 30.5 ;
        if ( $v = $interval->d >= 1 ) $diff = $diff + $interval->d;
        return $diff. 'روز' . $suffix;

    }

    public function pMonthDayFilter($date)
    {
        date_default_timezone_set('Asia/Tehran');
        //$jdf = new jdf();

        $jdate = new jDateTime();
        $gDate = $date->format('Y-m-d');
        $gDate = explode('-',$gDate);

        $date = $jdate->toJalali($gDate[0],$gDate[1],$gDate[2]);

        $month = '';
        switch($date[1]){
            case 1:
                $month = 'فروردین';
                break;
            case 2:
                $month = 'اردیبهشت';
                break;
            case 3:
                $month = 'خرداد';
                break;
            case 4:
                $month = 'تیر';
                break;
            case 5:
                $month = 'مرداد';
                break;
            case 6:
                $month = 'شهریور';
                break;
            case 7:
                $month = 'مهر';
                break;
            case 8:
                $month = 'آبان';
                break;
            case 9:
                $month = 'آذر';
                break;
            case 10:
                $month = 'دی';
                break;
            case 11:
                $month = 'بهمن';
                break;
            case 12:
                $month = 'اسفند';
                break;
        }

        return $date[2].' '.$month.' '.$date[0];
    }


    public function pDateFilter2(\DateTime $date = null) {

      if (!$date) {
        return ' ';
      }
      $gDate = $date->format('Y-m-d');

      $gDate = explode('-',$gDate);

      $jdate = new jDateTime();

      $date = $jdate->toJalali($gDate[0],$gDate[1],$gDate[2]);
        if ($date[0] < 0 || $date[1] < 0 || $date[2] < 0){
            return null;
        }
      return $date[0] .'/'. $date[1] .'/'.  $date[2];
    }

    public function pDateFilterString2($date = null) {

      return jDateTime::toJalaliByString($date);
    }

    public function pDateFilterMounth2($date) {

      return jDateTime::toJalaliMonthByString($date);

    }

    public function pDateFilterYear2($date) {

      return jDateTime::toJalaliYearByString($date);

    }

    public function removefirstcharacter($string)
    {
        $count = 3;
        return substr($string, intval($count));
    }
    
    
    
    public function pMonthDay2Filter($date)
    {
        date_default_timezone_set('Asia/Tehran');
        if($date == "now")
            $date = new \DateTime('');
        //$jdf = new jdf();

        $jdate = new jDateTime();
        $gDate = $date->format('Y-m-d');
        $gDate = explode('-',$gDate);

        $date = $jdate->toJalali($gDate[0],$gDate[1],$gDate[2]);

        $month = '';
        switch($date[1]){
            case 1:
                $month = 'فروردین';
                break;
            case 2:
                $month = 'اردیبهشت';
                break;
            case 3:
                $month = 'خرداد';
                break;
            case 4:
                $month = 'تیر';
                break;
            case 5:
                $month = 'مرداد';
                break;
            case 6:
                $month = 'شهریور';
                break;
            case 7:
                $month = 'مهر';
                break;
            case 8:
                $month = 'آبان';
                break;
            case 9:
                $month = 'آذر';
                break;
            case 10:
                $month = 'دی';
                break;
            case 11:
                $month = 'بهمن';
                break;
            case 12:
                $month = 'اسفند';
                break;
        }

        return 'امروز: '.$date[2].' '.$month.' '.$date[0];
    }

}
