DROP TABLE IF EXISTS `#__weatheropendata_products`;

CREATE TABLE `#__weatheropendata_products` (
  `id` int(11) NOT NULL auto_increment,
  `name` VARCHAR(255) NOT NULL DEFAULT '',
  `protocol` VARCHAR(10) NOT NULL DEFAULT '',
  `file` VARCHAR(255) NOT NULL DEFAULT '',
  `product` VARCHAR(20) NOT NULL DEFAULT '',
  `cache_minutes` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY  (`id`),
  INDEX `idx_name` (`name`)
) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;

INSERT INTO `#__weatheropendata_products` (`name`, `protocol`, `file`, `product`, `cache_minutes`) VALUES
  ('T_OD_VHS_VHDL17_DWOG', 'ftp', 'weather/text_forecasts/txt/ber01-VHDL17_DWOG*', 'wmo_txt', 60),
  ('T_OD_VHS_VHDL30_DWOH', 'ftp', 'weather/alerts/txt/OF/VHDL30_DWOH_*', 'wmo_txt', 60),
  ('T_OD_ANA_SXDL33_DWAV', 'ftp', 'weather/text_forecasts/txt/SXDL33_DWAV_*', 'sx33', 720),
  ('T_OD_VHS_VHDL50_DWEG', 'ftp', 'weather/text_forecasts/html/VHDL50_DWEG_LATEST_html', 'vhdl', 60),
  ('T_OD_VHS_VHDL35_DWOG', 'ftp', 'weather/alerts/txt/GER/VHDL35_DWOG_*', 'wmo_txt', 60),
  ('T_OD_VHS_VHDL50_DWEG_Schlagzeile', 'ftp', 'weather/text_forecasts/html//VHDL50_DWEG_LATEST_html', 'vhdl50_title', 60),
  ('T_OD_ANA_SXDL31_DWAV', 'ftp', 'weather/text_forecasts/txt/SXDL31_DWAV_*', 'sx31', 720),
  ('T_OD_ANA_FPDL13_DWMZ', 'ftp', 'weather/text_forecasts/txt/FPDL13_DWMZ*', 'fp13', 720),
  ('T_OD_ANA_FPDL13_DWMZ_TITEL', 'ftp', 'weather/text_forecasts/txt/FPDL13_DWMZ*', 'fp13_title', 720),
  ('B_OD__ANA_A_Format_Color', 'ftp', 'weather/charts/analysis/Z__C_EDZW_LATEST_tka01,ana_bwkman_dwda_O_000000_000000_LATEST_WV12.png', 'img', 180),
  ('K_ANA_NA_Bodenanalyse_DWD', 'https', 'www.dwd.de/DWD/wetter/wv_spez/hobbymet/wetterkarten/bwk_bodendruck_na_ana.png', 'img', 180),
  ('B_WWW_VHS_DL_UV_Index_ICON_Tag2', 'https', 'www.dwd.de/DWD/warnungen/medizin/uvi/uve_cli_12_region_2.png', 'img', 360),
  ('B_WWW_VHS_DL_UV_Index_ICON_Tag1', 'https', 'www.dwd.de/DWD/warnungen/medizin/uvi/uve_cli_12_region_1.png', 'img', 360),
  ('B_WWW_VHS_DL_UV_Index_ICON_Tag0', 'https', 'www.dwd.de/DWD/warnungen/medizin/uvi/uve_cli_12_region_0.png', 'img', 360);
