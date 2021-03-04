<?php 

class JohnDeLancie {
    public static function getTime($onBlog) {
        $blog = new Blog($onBlog);
        if ($blog->failed) {
            return new DateTime();
        }
        $queueRangeStart = $blog->settings['queueRangeStart'];
        $queueRangeEnd = $blog->settings['queueRangeEnd'];
        $frequency = $blog->settings['queueFrequency'];
        $fuzz = $blog->settings['fuzzQueue'];
        if ($queueRangeStart == $queueRangeEnd) {
            $queueRangeEnd = $queueRangeEnd + 1;
        }
        if ($queueRangeEnd > 23) {
            if ($queueRangeStart != 23) {
                $queueRangeEnd = 23;
            } else {
                $queueRangeEnd = 22;
            }
        }
        $hoursInRange = abs($queueRangeEnd - $queueRangeStart);
        if ($hoursInRange == 0) {
            return new DateTime();
        }
        $countInQueue = $blog->getCountInQueue();
        $secondsInRange = $hoursInRange * 3600;
        $secondsPerPost = $secondsInRange / $frequency;
        $uncleanPosts = $countInQueue % $frequency; // Gives the number of posts on the next not-full day
        if ($countInQueue == $frequency) {
            $daysInFuture = 2;
        } else {
            $daysInFuture = ceil($countInQueue / $frequency);
        }
        $dateToPost = new DateTime();
        if ($daysInFuture == 0) {
            $daysInFuture == 1;
        }
        $dateToPost->modify('+'.$daysInFuture.' days');
        if ($daysInFuture != 0) {
            $dateToPost->setTime($queueRangeStart, $dateToPost->format('i'));
        }
        $modifySeconds = ($uncleanPosts + 1) * $secondsPerPost;
        $dateToPost->modify('+'.$modifySeconds.' seconds');
        if ($fuzz == true) {
            $dateToPost->modify('+'.rand(-600, 600).' seconds');
        }
        return $dateToPost;
    }
}