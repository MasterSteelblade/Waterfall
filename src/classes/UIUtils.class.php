<?php 
/**
 * @class WFUtils
 * @brief A class for providing miscellaneous UI functions.
 *
 * Basic set of static functions that do useful things. 
 * @author Benjamin Clarke
*/

class UIUtils {
    // All functions should be public static.
    public static function errorBox($message, $title = '', $allowClose = false) {
        /** Displays an error message.
        *
        * @param message The text to display.
        */
        echo "<div class=\"alert alert-danger w-100\" role=\"alert\">";
        if ($title != '') {
          echo "<h4>$title</h4><p>";
        }
        echo $message;
        if ($title != '') {
          echo '</p>';
        }
        if ($allowClose) {
          echo '  <button type="button" class="close" data-dismiss="alert" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>';
        }
        echo "</div>";
      
      }
      
    public static function successBox($message, $title = '', $allowClose = false) {
        /** Displays a success message.
        *
        * @param message The text to display.
        */
        echo "<div class=\"alert alert-success w-100\" role=\"alert\">";
        if ($title != '') {
          echo "<h4>$title</h4><p>";
        }
        echo $message;
        if ($title != '') {
          echo '</p>';
        }
        if ($allowClose) {
          echo '  <button type="button" class="close" data-dismiss="alert" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>';
        }
        echo "</div>";
      
      }
      
      
    public static function infoBox($message, $title = '', $allowClose = false) {
        /** Displays an info message.
        *
        * @param message The text to display.
        */
        echo "<div class=\"alert alert-info w-100\" role=\"alert\">";
        if ($title != '') {
          echo "<h4>$title</h4><p>";
        }
        echo $message;
        if ($title != '') {
          echo '</p>';
        }
        if ($allowClose) {
          echo '  <button type="button" class="close" data-dismiss="alert" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>';
        }
        echo "</div>";
      }
      
      
      
    public static function warnBox($message, $title = '', $allowClose = false) {
        /** Displays an info message.
        *
        * @param message The text to display.
        */
        echo "<div class=\"alert alert-warning w-100\" role=\"alert\">";
        if ($title != '') {
          echo "<h4>$title</h4><p>";
        }
        echo $message;
        if ($title != '') {
          echo '</p>';
        }
        if ($allowClose) {
          echo '  <button type="button" class="close" data-dismiss="alert" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>';
        }
        echo "</div>";
      }

      public static function primaryBox($message, $title = '', $allowClose = false) {
        /** Displays a primary alert message.
        *
        * @param message The text to display.
        */
        echo "<div class=\"alert alert-primary w-100\" role=\"alert\">";
        if ($title != '') {
          echo "<h4>$title</h4><p>";
        }
        echo $message;
        if ($title != '') {
          echo '</p>';
        }
        if ($allowClose) {
          echo '  <button type="button" class="close" data-dismiss="alert" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>';
        }
        echo "</div>";
      
      }
      public static function secondaryBox($message, $title = '', $allowClose = false) {
        /** Displays a secondary alert message.
        *
        * @param message The text to display.
        */
        echo "<div class=\"alert alert-danger w-100\" role=\"alert\">";
        if ($title != '') {
          echo "<h4>$title</h4><p>";
        }
        echo $message;
        if ($title != '') {
          echo '</p>';
        }
        if ($allowClose) {
          echo '  <button type="button" class="close" data-dismiss="alert" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>';
        }
        echo "</div>";
      
      }

    }