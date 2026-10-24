# 【このファイルは？】
# ログイン状態をデータベースに保存するときに必要な、表の設計図。今は使っていない。
#
# 【担当】なし（見るだけ。変えたいときは A チームに相談）
# 【くわしく】docs/FILES.md

# Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
#
# Licensed under The MIT License
# For full copyright and license information, please see the LICENSE.txt
# Redistributions of files must retain the above copyright notice.
# MIT License (https://opensource.org/licenses/mit-license.php)

CREATE TABLE `sessions` (
  `id` char(40) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  `created` datetime DEFAULT CURRENT_TIMESTAMP, -- optional, requires MySQL 5.6.5+
  `modified` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, -- optional, requires MySQL 5.6.5+
  `data` blob DEFAULT NULL, -- for PostgreSQL use bytea instead of blob
  `expires` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
