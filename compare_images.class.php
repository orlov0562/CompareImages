<?php

    class compare_images
    {
        // minimal hamming length to determinate dublicate
        private static $compare_hash_precision = 100;

        // image hash size, for example: 16 = image sized to 16px*16px and we have 256 b&w dots in hash
        private static $mask_image_size = 16;

        public static function compare($image1_filename, $image2_filename)
        {
            $image1_hash = self::get_image_hash($image1_filename);
            $image2_hash = self::get_image_hash($image2_filename);
            return self::is_identical($image1_hash, $image2_hash);
        }

        public static function compare_ext($image1_filename, $image2_filename)
        {
            $image1_hash = self::get_image_hash($image1_filename);
            $image2_hash = self::get_image_hash($image2_filename);
            return array(
                'identical'=>self::is_identical($image1_hash, $image2_hash),
                'hamming'=>self::hamming($image1_hash, $image2_hash),
            );
        }

        public static function compare_hash($image1_hash, $image2_hash)
        {
            return self::is_identical($image1_hash, $image2_hash);
        }

        public static function compare_hash_ext($image1_hash, $image2_hash)
        {
            return array(
                'identical'=>self::is_identical($image1_hash, $image2_hash),
                'hamming'=>self::hamming($image1_hash, $image2_hash),
            );
        }

        public static function get_image_hash($image1_filename)
        {
            $ret = FALSE;
            $im = self::get_image_from_file($image1_filename);
            if ($im)
            {
                $im = self::resize_image_to_mask_size($im);
                $im = self::convert_to_gray($im);
                $im = self::add_color_matrix($im);
                $avg_color = self::get_avg_color($im);
                $ret = self::get_binary_hash($im, $avg_color);
            }
            return $ret;
        }

        private static function get_image_from_file( $filename )
        {
            $ret = FALSE;
            $image_vars = getimagesize( $filename );
            $src_type = $image_vars[2];

            if (in_array($src_type, array(IMAGETYPE_JPEG, IMAGETYPE_GIF, IMAGETYPE_PNG)))
            {
                $ret = new stdClass;
                $ret->width = $image_vars[0];
                $ret->height = $image_vars[1];

                switch ($src_type) {
                    case IMAGETYPE_JPEG:
                        $ret->image = imagecreatefromjpeg($filename);
                        break;
                    case IMAGETYPE_GIF:
                        $ret->image = imagecreatefromgif($filename);
                        break;
                    case IMAGETYPE_PNG:
                        $ret->image = imagecreatefrompng($filename);
                        break;
                }

                if (!is_resource($ret->image)) return FALSE;

            }

            return $ret;
        }

        private static function resize_image_to_mask_size($im)
        {
            $mask_image = imagecreatetruecolor(self::$mask_image_size, self::$mask_image_size);

            $src_image_size = $im->width < $im->height ? $im->width : $im->height;

            $result = imagecopyresized(   $mask_image, $im->image,
                                          0,  0,  0,  0,
                                          self::$mask_image_size, self::$mask_image_size,
                                          $src_image_size, $src_image_size
            );

            if ($result)
            {
                $im->image = $mask_image;
            }

            return $im;
        }

        private static function convert_to_gray($im)
        {
            imagefilter($im->image, IMG_FILTER_GRAYSCALE);
            return $im;
        }

        private static function add_color_matrix($im)
        {
            $im->color = array();
            for ($x=0; $x<self::$mask_image_size; $x++)
            {
                for ($y=0; $y<self::$mask_image_size; $y++)
                {
                    $color = imagecolorat($im->image, $x, $y );
                    $rgb = imagecolorsforindex($im->image,  $color);
                    $rgb['gray'] = floor(($rgb['red']+$rgb['blue']+$rgb['green'])/3);
                    $im->color[$x][$y] = $rgb;
                }
            }
            return $im;
        }

        private static function get_avg_color($im)
        {
            $ret = 0;
            for ($x=0; $x<self::$mask_image_size; $x++)
            {
                for ($y=0; $y<self::$mask_image_size; $y++)
                {
                    $ret += $im->color[$x][$y]['gray'];
                }
            }
            $ret = floor( $ret / (self::$mask_image_size*self::$mask_image_size) );
            return $ret;
        }

        private static function get_binary_hash($im, $avg_color)
        {
            $ret = '';
            for ($x=0; $x<self::$mask_image_size; $x++)
            {
                for ($y=0; $y<self::$mask_image_size; $y++)
                {
                    $ret.= $im->color[$x][$y]['gray'] < $avg_color ? '0' : '1';
                }
            }
            return $ret;
        }

        private static function is_identical($image1_hash, $image2_hash)
        {
            return self::hamming($image1_hash, $image2_hash) <= self::$compare_hash_precision;
        }

        private static function hamming($b1, $b2) {
            $b1 = ltrim($b1, '0');
            $b2 = ltrim($b2, '0');
            $l1 = strlen($b1);
            $l2 = strlen($b2);
            $n = min($l1, $l2);
            $d = max($l1, $l2) - $n;
            for ($i=0; $i<$n; ++$i) {
                if ($b1[$l1-$i] != $b2[$l2-$i]) {
                    ++$d;
                }
            }
            return $d;
        }

        private static function image_out($im)
        {
            header('Content-type: image/png');
            imagepng($im->image);
        }
    }
