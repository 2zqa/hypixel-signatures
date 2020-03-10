<?php

    namespace App\Utilities\MinecraftAvatar;

    use Log;
    use RuntimeException;

    /**
     * Class MCavatar
     * Modified source code made into class
     *
     * @author      Max Korlaar
     * @description Class to make it easier to get Minecraft skins.
     * @license     MIT
     *              Some code was borrowed from an old opensource github project, which was not
     *              working very well.
     *              TODO Find old URL
     */
    class MCavatar {
        public const STEVE_SKIN = 'https://hypixel.maxkorlaar.com/img/Steve_skin.png';
        public $name;
        public $skinurl;
        public $fetchUrl;
        public $size;
        public $imagepath;
        public $cacheInfo;
        public $publicurl;
        public $helm = true;
        public $fetchError = null;

        /**
         * Defines url
         */
        public function __construct() {
            $this->skinurl   = 'http://skins.minecraft.net/MinecraftSkins/';
            $this->imagepath = storage_path('app/public/minecraft-avatars') . '/';
        }

        /**
         * @param      $username
         * @param bool $save
         *
         * @return string
         */
        public function getSkinFromCache($username, $save = true) {
            $imagepath       = $this->imagepath . 'full_skin/' . strtolower($username) . '.png';
            $this->publicurl = '/img/full_skin/' . strtolower($username) . '.png';

            if (file_exists($imagepath)) {
                if (filemtime($imagepath) < strtotime('-2 week')) {
                    $this->cacheInfo = 'full skin expired, redownloading';
                    unlink($imagepath);
                    return $this->getSkin($username, $save);
                }

                return $imagepath;
            }

            $this->cacheInfo = 'full skin image not yet downloaded';
            return $this->getSkin($username, $save);
        }

        /**
         * @param      $username
         * @param bool $save
         *
         * @return resource|string
         */
        public function getSkin($username, $save = false) {
            $this->publicurl = '/img/full_skin/' . strtolower($username) . '.png';
            $this->cacheInfo = 'Downloading skin from Minecraft.net...';
            $skinURL         = 'https://minecraft.net/images/steve.png';
            $this->cacheInfo = 'Downloaded from ' . $skinURL;
            if (strlen($username) === 32) {
                $api  = new MojangAPI();
                $data = $api->getProfile($username);
                if ($data['success'] === true) {
                    $skinData = $data['data'];
                    if ($skinData['skinURL'] === null) {
                        $imgURL          = $skinData['isSteve'] ? self::STEVE_SKIN : 'https://minecraft.net/images/alex.png';
                        $this->cacheInfo = 'image not yet downloaded - default';
                        Log::debug('image not yet downloaded - default');
                    } else {
                        $imgURL = $skinData['skinURL'];

                    }
                    $this->fetchUrl = $imgURL;
                    $src            = imagecreatefrompng($imgURL);
                    if (!$src) {
                        Log::debug('Source is false', [$this->fetchUrl]);
                        $src              = imagecreatefrompng(self::STEVE_SKIN);
                        $this->fetchError = true;
                        $save             = false;
                    }
                    $this->cacheInfo = 'Downloaded from ' . $imgURL;
                    Log::debug('Downloaded from ' . $imgURL);
                } else {
                    $src             = imagecreatefrompng(self::STEVE_SKIN);
                    $this->cacheInfo = 'image not yet downloaded - unknown error while getting player profile';
                    Log::debug('image not yet downloaded - unknown error while getting player profile', [$data]);
                    $this->fetchError = true;
                    $save             = false;
                }
            } else {
                //$src            = @imagecreatefrompng("http://skins.minecraft.net/MinecraftSkins/{$username}.png");
                //$this->fetchUrl = "http://skins.minecraft.net/MinecraftSkins/{$username}.png";
                $api  = new MojangAPI();
                $uuid = $api->getUUID($username);
                if ($uuid['success']) {
                    return $this->getSkin($uuid['data']['id'], $save);
                }

                $src             = imagecreatefrompng(self::STEVE_SKIN);
                $this->cacheInfo = 'image not yet downloaded - unknown error while fetching skin from username. Last resort: ' . self::STEVE_SKIN;
                Log::debug('image not yet downloaded - unknown error while fetching skin from username. Last resort: ' . self::STEVE_SKIN);
                $this->fetchError = true;
                $save             = false;
            }

            imageAlphaBlending($src, true);
            imageSaveAlpha($src, true);
            if ($save) {
                $imagepath = $this->imagepath . 'full_skin/' . strtolower($username) . '.png';
                if (!file_exists($this->imagepath . 'full_skin/') && !mkdir($concurrentDirectory = $this->imagepath . 'full_skin/', 0777, true) && !is_dir($concurrentDirectory)) {
                    throw new RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
                }
                imagepng($src, $imagepath);
                return $imagepath;
            }

            return $src;
        }

        /**
         * @param      $username
         * @param int  $size
         * @param bool $helm
         *
         * @usage getFromCache('MegaMaxsterful');
         * @return string
         */
        public function getFromCache($username, $size = 100, $helm = true): string {
            if ($helm) {
                $imagepath       = $this->imagepath . $size . 'px/' . strtolower($username) . '.png';
                $this->publicurl = '/img/' . $size . 'px/' . strtolower($username) . '.png';
            } else {
                $imagepath       = $this->imagepath . $size . 'px-no-helm/' . strtolower($username) . '.png';
                $this->publicurl = '/img/' . $size . 'px-no-helm/' . strtolower($username) . '.png';
            }
            $this->name = $username;
            $this->size = $size;
            $this->helm = $helm;

            if (file_exists($imagepath)) {
                if (filemtime($imagepath) < strtotime('-2 week')) {
                    $this->cacheInfo = 'expired, redownloading';
                    unlink($imagepath);
                    return $this->getImage($username, $size, $helm);
                }

                $this->cacheInfo = 'not expired';
                return $imagepath;
            }

            $this->cacheInfo = 'image not yet downloaded';
            return $this->getImage($username, $size, $helm);
        }

        /**
         * @param      $username
         * @param int  $size
         * @param bool $helm
         * @param bool $save
         *
         * @return string
         */
        public function getImage($username, $size = 100, $helm = true, $save = true): string {
            $this->name  = $username;
            $this->size  = $size;
            $defaultSkin = null;
            if ($helm) {
                $this->publicurl = '/img/' . $size . 'px/' . strtolower($username) . '.png';
            } else {
                $this->publicurl = '/img/' . $size . 'px-no-helm/' . strtolower($username) . '.png';
            }

            if (strlen($username) === 32) {
                $api  = new MojangAPI();
                $data = $api->getProfile($username);
                if ($data['success'] === true) {
                    $skinData = $data['data'];
                    if ($skinData['skinURL'] === null) {
                        $imgURL          = $skinData['isSteve'] ? self::STEVE_SKIN : 'https://minecraft.net/images/alex.png';
                        $this->cacheInfo = 'image not yet downloaded - default';
                    } else {
                        $imgURL = $skinData['skinURL'];

                    }
                    $this->fetchUrl = $imgURL;
                    $src            = imagecreatefrompng($imgURL);
                    if (!$src) {
                        $src              = imagecreatefrompng(self::STEVE_SKIN);
                        $this->cacheInfo  = 'image not yet downloaded - unknown error while downloading';
                        $defaultSkin      = 'steve';
                        $this->fetchError = true;
                        $save             = false;
                    }
                } else {
                    $src              = imagecreatefrompng(self::STEVE_SKIN);
                    $this->cacheInfo  = 'image not yet downloaded - unknown error while getting player profile';
                    $defaultSkin      = 'steve';
                    $this->fetchError = true;
                    $save             = false;
                }
            } else {
                $src            = imagecreatefrompng("http://skins.minecraft.net/MinecraftSkins/{$username}.png");
                $this->fetchUrl = "http://skins.minecraft.net/MinecraftSkins/{$username}.png";
                if (!$src) {
                    $src              = imagecreatefrompng(self::STEVE_SKIN);
                    $this->cacheInfo  = 'image not yet downloaded - unknown error while fetching skin from username';
                    $defaultSkin      = 'steve';
                    $this->fetchError = true;
                    $save             = false;
                }
            }

            $dest = imagecreatetruecolor(8, 8);
            imagecopy($dest, $src, 0, 0, 8, 8, 8, 8);
            if ($helm) {
                $bg_color = imagecolorat($src, 0, 0);
                $no_helm  = true;
                for ($i = 1; $i <= 8; $i++) {
                    for ($j = 1; $j <= 4; $j++) {
                        if (imagecolorat($src, 39 + $i, 7 + $j) !== $bg_color) {
                            $no_helm = false;
                        }
                    }

                    if (!$no_helm) {
                        break;
                    }
                }
                if (!$no_helm) {
                    imagecopy($dest, $src, 0, 0, 40, 8, 8, 8);
                }
            }
            $final = imagecreatetruecolor($size, $size);
            imagecopyresized($final, $dest, 0, 0, 0, 0, $size, $size, 8, 8);
            if ($helm) {
                if (!file_exists($this->imagepath . $size . 'px/') && !mkdir($concurrentDirectory = $this->imagepath . $size . 'px/', 0777, true) && !is_dir($concurrentDirectory)) {
                    throw new RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
                }
                $imagepath = $this->imagepath . $size . 'px/' . strtolower($username) . '.png';
            } else {
                if (!file_exists($this->imagepath . $size . 'px-no-helm/') && !mkdir($concurrentDirectory = $this->imagepath . $size . 'px-no-helm/', 0777, true) && !is_dir($concurrentDirectory)) {
                    throw new RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
                }
                $imagepath = $this->imagepath . $size . 'px-no-helm/' . strtolower($username) . '.png';
            }

            if ($save) {
                imagepng($final, $imagepath);
            }
            if ($defaultSkin !== null) {
                $imagepath = $this->imagepath . $size . 'px/' . $defaultSkin . '.png';
                imagepng($final, $imagepath);
            }
            return $imagepath;
        }

        /**
         * @return mixed
         */
        public function getName() {
            return $this->name;
        }

        /**
         * @param mixed $name
         */
        public function setName($name): void {
            $this->name = $name;
        }
    }
