<?php
mb_internal_encoding("UTF-8");
mb_regex_encoding('UTF-8');

require('oxy_dictionary.php');
if (false) { class oxy extends _oxy {} }
abstract class _oxy extends _oxy_dictionary {

	//
	//
	// Icons
	//
	//

	// 000 - Basic
	public static function icoSpacer                (){ return new Glyph('oxy-icon',0xE000); }
	public static function icoBlock                 (){ return new Glyph('oxy-icon',0xE001); }
	public static function icoItem                  (){ return new Glyph('oxy-icon',0xE001); }
	public static function icoItems                 (){ return new Glyph('oxy-icon',0xE002); }
	public static function icoUser                  (){ return new Glyph('oxy-icon',0xE003); }
	public static function icoUsers                 (){ return new Glyph('oxy-icon',0xE004); }
	public static function icoSettings              (){ return new Glyph('oxy-icon',0xE005); }
	public static function icoEmail                 (){ return new Glyph('oxy-icon',0xE006); }
	public static function icoPrint                 (){ return new Glyph('oxy-icon',0xE007); }
	public static function icoComment               (){ return new Glyph('oxy-icon',0xE008); }
	public static function icoKey                   (){ return new Glyph('oxy-icon',0xE009); }
	public static function icoFavorite              (){ return new Glyph('oxy-icon',0xE00A); }
	public static function icoTime                  (){ return new Glyph('oxy-icon',0xE00C); }
	public static function icoDate                  (){ return new Glyph('oxy-icon',0xE00D); }
	public static function icoEmpty                 (){ return new Glyph('oxy-icon',0xE00E); }
	public static function icoAll                   (){ return new Glyph('oxy-icon',0xE00F); }
	public static function icoAsterisk              (){ return new Glyph('oxy-icon',0xE010); }
	public static function icoNew                   (){ return new Glyph('oxy-icon',0xE011); }
	public static function icoForbidden             (){ return new Glyph('oxy-icon',0xE012); }
	public static function icoDocument              (){ return new Glyph('oxy-icon',0xE013); }
	public static function icoFile                  (){ return new Glyph('oxy-icon',0xE013); }
	public static function icoAttachment            (){ return new Glyph('oxy-icon',0xE014); }
	public static function icoSupport               (){ return new Glyph('oxy-icon',0xE015); }

	// 100 - Messages
	public static function icoInfo                  (){ return new Glyph('oxy-icon',0xE100); }
	public static function icoSuccess               (){ return new Glyph('oxy-icon',0xE101); }
	public static function icoWarning               (){ return new Glyph('oxy-icon',0xE102); }
	public static function icoQuestion              (){ return new Glyph('oxy-icon',0xE103); }
	public static function icoSecurity              (){ return new Glyph('oxy-icon',0xE104); }
	public static function icoError                 (){ return new Glyph('oxy-icon',0xE105); }
	public static function icoBug                   (){ return new Glyph('oxy-icon',0xE106); }

	// 200 - Basic Actions
	public static function icoView                  (){ return new Glyph('oxy-icon',0xE200); }
	public static function icoModify                (){ return new Glyph('oxy-icon',0xE201); }
	public static function icoRename                (){ return new Glyph('oxy-icon',0xE202); }
	public static function icoRefresh               (){ return new Glyph('oxy-icon',0xE203); }
	public static function icoUndo                  (){ return new Glyph('oxy-icon',0xE204); }
	public static function icoRedo                  (){ return new Glyph('oxy-icon',0xE205); }
	public static function icoLogin                 (){ return new Glyph('oxy-icon',0xE206); }
	public static function icoLogoff                (){ return new Glyph('oxy-icon',0xE207); }
	public static function icoSelect                (){ return new Glyph('oxy-icon',0xE208); }
	public static function icoReset                 (){ return new Glyph('oxy-icon',0xE203); }
	public static function icoFavoritize            (){ return new Glyph('oxy-icon',0xE00A); }
	public static function icoUnfavoritize          (){ return new Glyph('oxy-icon',0xE00B); }

	// 500/600 - Circle/Simple Actions
	public static function icoBrowse                (){ return new Glyph('oxy-icon',0xE500); }
	public static function icoList                  (){ return new Glyph('oxy-icon',0xE600); }
	public static function icoAdd                   (){ return new Glyph('oxy-icon',0xE501); }
	public static function icoCreate                (){ return new Glyph('oxy-icon',0xE501); }
	public static function icoPlus                  (){ return new Glyph('oxy-icon',0xE601); }
	public static function icoRemove                (){ return new Glyph('oxy-icon',0xE502); }
	public static function icoMinus                 (){ return new Glyph('oxy-icon',0xE602); }
	public static function icoCancel                (){ return new Glyph('oxy-icon',0xE503); }
	public static function icoDelete                (){ return new Glyph('oxy-icon',0xE503); }
	public static function icoX                     (){ return new Glyph('oxy-icon',0xE603); }
	public static function icoNo                    (){ return new Glyph('oxy-icon',0xE603); }
	public static function icoAccept                (){ return new Glyph('oxy-icon',0xE504); }
	public static function icoOK                    (){ return new Glyph('oxy-icon',0xE504); }
	public static function icoTick                  (){ return new Glyph('oxy-icon',0xE604); }
	public static function icoYes                   (){ return new Glyph('oxy-icon',0xE604); }
	public static function icoHelp                  (){ return new Glyph('oxy-icon',0xE505); }
	public static function icoQuestionMark          (){ return new Glyph('oxy-icon',0xE605); }
	public static function icoSearch                (){ return new Glyph('oxy-icon',0xE506); }
	public static function icoSearchGlass           (){ return new Glyph('oxy-icon',0xE606); }
	public static function icoGoToParent            (){ return new Glyph('oxy-icon',0xE507); }
	public static function icoParent                (){ return new Glyph('oxy-icon',0xE607); }
	public static function icoMoveUp                (){ return new Glyph('oxy-icon',0xE508); }
	public static function icoUpload                (){ return new Glyph('oxy-icon',0xE508); }
	public static function icoUp                    (){ return new Glyph('oxy-icon',0xE608); }
	public static function icoMoveDown              (){ return new Glyph('oxy-icon',0xE509); }
	public static function icoDownload              (){ return new Glyph('oxy-icon',0xE509); }
	public static function icoDown                  (){ return new Glyph('oxy-icon',0xE609); }
	public static function icoMoveLeft              (){ return new Glyph('oxy-icon',0xE50A); }
	public static function icoBack                  (){ return new Glyph('oxy-icon',0xE50A); }
	public static function icoLeft                  (){ return new Glyph('oxy-icon',0xE60A); }
	public static function icoMove                  (){ return new Glyph('oxy-icon',0xE50B); }
	public static function icoMoveRight             (){ return new Glyph('oxy-icon',0xE50B); }
	public static function icoForward               (){ return new Glyph('oxy-icon',0xE50B); }
	public static function icoRight                 (){ return new Glyph('oxy-icon',0xE60B); }
	public static function icoDuplicate             (){ return new Glyph('oxy-icon',0xE50C); }
	public static function icoDouble                (){ return new Glyph('oxy-icon',0xE60C); }
	public static function icoLock                  (){ return new Glyph('oxy-icon',0xE50D); }
	public static function icoLocked                (){ return new Glyph('oxy-icon',0xE60D); }
	public static function icoFree                  (){ return new Glyph('oxy-icon',0xE50E); }
	public static function icoUnlock                (){ return new Glyph('oxy-icon',0xE50E); }
	public static function icoUnlocked              (){ return new Glyph('oxy-icon',0xE60E); }
	public static function icoAction                (){ return new Glyph('oxy-icon',0xE50F); }
	public static function icoApply                 (){ return new Glyph('oxy-icon',0xE50F); }
	public static function icoBatch                 (){ return new Glyph('oxy-icon',0xE50F); }
	public static function icoThunder               (){ return new Glyph('oxy-icon',0xE60F); }
	public static function icoPlay                  (){ return new Glyph('oxy-icon',0xE510); }
	public static function icoPlaySymbol            (){ return new Glyph('oxy-icon',0xE610); }
	public static function icoPause                 (){ return new Glyph('oxy-icon',0xE511); }
	public static function icoPauseSymbol           (){ return new Glyph('oxy-icon',0xE611); }
	public static function icoStop                  (){ return new Glyph('oxy-icon',0xE512); }
	public static function icoStopSymbol            (){ return new Glyph('oxy-icon',0xE612); }
	public static function icoPlayPrev              (){ return new Glyph('oxy-icon',0xE513); }
	public static function icoPlayPrevSymbol        (){ return new Glyph('oxy-icon',0xE613); }
	public static function icoPlayNext              (){ return new Glyph('oxy-icon',0xE514); }
	public static function icoPlayNextSymbol        (){ return new Glyph('oxy-icon',0xE614); }
	public static function icoFastBackward          (){ return new Glyph('oxy-icon',0xE515); }
	public static function icoFastBackwardSymbol    (){ return new Glyph('oxy-icon',0xE615); }
	public static function icoFastForward           (){ return new Glyph('oxy-icon',0xE516); }
	public static function icoFastForwardSymbol     (){ return new Glyph('oxy-icon',0xE616); }
	public static function icoZoomIn                (){ return new Glyph('oxy-icon',0xE517); }
	public static function icoZoomInSymbol          (){ return new Glyph('oxy-icon',0xE617); }
	public static function icoZoomOut               (){ return new Glyph('oxy-icon',0xE518); }
	public static function icoZoomOutSymbol         (){ return new Glyph('oxy-icon',0xE618); }

	// 900 - Interface
	public static function icoContextMenuAnchor     (){ return new Glyph('oxy-icon',0xE900); }
	public static function icoMore                  (){ return new Glyph('oxy-icon',0xE902); }
	public static function icoClear                 (){ return new Glyph('oxy-icon',0xE901); }
	public static function icoDropArea              (){ return new Glyph('oxy-icon',0xE903); }
	public static function icoMenuUp                (){ return new Glyph('oxy-icon',0xE904); }
	public static function icoMenuDown              (){ return new Glyph('oxy-icon',0xE905); }
	public static function icoMenuRight             (){ return new Glyph('oxy-icon',0xE906); }
	public static function icoMenuLeft              (){ return new Glyph('oxy-icon',0xE907); }
	public static function icoOrderAsc              (){ return new Glyph('oxy-icon',0xE908); }
	public static function icoOrderDesc             (){ return new Glyph('oxy-icon',0xE909); }
	public static function icoWideView              (){ return new Glyph('oxy-icon',0xE910); }
	public static function icoVolume                (){ return new Glyph('oxy-icon',0xE911); }
	public static function icoNoVolume              (){ return new Glyph('oxy-icon',0xE912); }

	public static function icoWiFiLevel1       (){ return new Glyph('oxy-icon',0xE913); }
	public static function icoWiFiLevel2       (){ return new Glyph('oxy-icon',0xE914); }
	public static function icoWiFiLevel3       (){ return new Glyph('oxy-icon',0xE915); }
	public static function icoWiFiLevel4       (){ return new Glyph('oxy-icon',0xE916); }
	/** @return Glyph */
	public static function icoWiFiLevel($decibel){
		if ($decibel > -50) return self::icoWiFiLevel4()->WithTitle($decibel.' dBm')->WithCssClass('help');
		if ($decibel > -60) return self::icoWiFiLevel3()->WithTitle($decibel.' dBm')->WithCssClass('help');
		if ($decibel > -70) return self::icoWiFiLevel2()->WithTitle($decibel.' dBm')->WithCssClass('help');
		return self::icoWiFiLevel1()->WithTitle($decibel.' dBm')->WithCssClass('help');
	}

	public static function icoBoxUnchecked          (){ return new Glyph('oxy-icon',0xE9A0); }
	public static function icoBoxChecked            (){ return new Glyph('oxy-icon',0xE9A1); }
	public static function icoBoxDirty              (){ return new Glyph('oxy-icon',0xE9A3); }
	public static function icoTreePlus              (){ return new Glyph('oxy-icon',0xE9A2); }
	public static function icoTreeMinus             (){ return new Glyph('oxy-icon',0xE9A3); }
	public static function icoTreeDot               (){ return new Glyph('oxy-icon',0xE9A4); }
	public static function icoBoxCheckedFalse       (){ return new Glyph('oxy-icon',0xE9A5); }

	public static function icoBoxUncheckedLocked    (){ return new Glyph('oxy-icon',0xE9B0); }
	public static function icoBoxCheckedLocked      (){ return new Glyph('oxy-icon',0xE9B1); }
	public static function icoBoxDirtyLocked        (){ return new Glyph('oxy-icon',0xE9B3); }
	public static function icoTreePlusLocked        (){ return new Glyph('oxy-icon',0xE9B2); }
	public static function icoTreeMinusLocked       (){ return new Glyph('oxy-icon',0xE9B3); }
	public static function icoTreeDotLocked         (){ return new Glyph('oxy-icon',0xE9B4); }
	public static function icoBoxCheckedFalseLocked (){ return new Glyph('oxy-icon',0xE9B5); }

	public static function icoOptionUnchecked       (){ return new Glyph('oxy-icon',0xE9C0); }
	public static function icoOptionChecked         (){ return new Glyph('oxy-icon',0xE9C1); }
	public static function icoOptionDirty           (){ return new Glyph('oxy-icon',0xE9C2); }
	public static function icoOptionUncheckedLocked (){ return new Glyph('oxy-icon',0xE9D0); }
	public static function icoOptionCheckedLocked   (){ return new Glyph('oxy-icon',0xE9D1); }
	public static function icoOptionDirtyLocked     (){ return new Glyph('oxy-icon',0xE9D2); }


	public static function icoFillTopLeft       (){ return new Glyph('oxy-icon',0xE9E1); }
	public static function icoFillLeft          (){ return new Glyph('oxy-icon',0xE9E2); }
	public static function icoFillBottomLeft    (){ return new Glyph('oxy-icon',0xE9E3); }
	public static function icoFillTop           (){ return new Glyph('oxy-icon',0xE9E4); }
	public static function icoFillFull          (){ return new Glyph('oxy-icon',0xE9E5); }
	public static function icoFillBottom        (){ return new Glyph('oxy-icon',0xE9E6); }
	public static function icoFillTopRight      (){ return new Glyph('oxy-icon',0xE9E7); }
	public static function icoFillRight         (){ return new Glyph('oxy-icon',0xE9E8); }
	public static function icoFillBottomRight   (){ return new Glyph('oxy-icon',0xE9E9); }

	//
	//
	// Formating
	//
	//
	public static function FormatDate( XDateTime $date = null ){ return Language::FormatDate( $date  );	}
	public static function FormatDateTime( XDateTime $date = null ){ return Language::FormatDateTime( $date ); }
	public static function FormatTime( XDateTime $time = null ){		return Language::FormatDateTime( $time );	}
	public static function FormatDateTime24( XDateTime $date = null ){ if ($date!==null&&$date->Format('His')=='000000') return static::FormatDate( $date->AddDays(-1) ).' 24:00:00'; else return static::FormatDateTime( $date ); }
	public static function FormatDateSpanSince( XDateTime $date = null ){		return Language::FormatDateSpanSince( $date );	}
	public static function FormatTimeSpan( XTimeSpan $timespan = null ){		return Language::FormatTimeSpan( $timespan );	}
	public static function FormatTimeSpanSince( XDateTime $date = null ){		return Language::FormatTimeSpanSince( $date );	}
	public static function FormatDateTimeRelatively( XDateTime $date = null ){		return Language::FormatDateTimeRelatively( $date );	}
	public static function FormatBytes( $bytes ) {		return Language::FormatBytes($bytes);	}
	public static function FormatTimeZone( $timezone ) { return str_replace(['/','_'],[' / ',' '],ltrim($timezone,'/')); }
	public static function FormatNumber( $number , $decimals = -1 ) { return  Language::FormatNumber($number,$decimals); }
	public static function FormatNumberInvariant( $number , $decimals = -1 ) { return  Language::FormatNumberInvariant($number,$decimals); }
	public static function FormatNumberSI( $number , $decimals = -1 ) { return  Language::FormatNumberSI($number,$decimals); }

	public static function FormatDateTimeTZInvariant( XDateTime $date = null ){ return Oxygen::WithServerTimeZone(function()use($date){ return oxy::FormatDateTime($date); }); }

	public static function AbbrSeconds( XTimeSpan $timespan = null , $number_of_decimals = -1 ){ return $timespan===null ? null : '<span style="display:none;">'.sprintf('%020d',$timespan->GetTotalMilliSeconds()).'"</span><abbr title="'. new XmlAttr(Language::FormatTimeSpan($timespan)).'">'.new Html( Language::FormatNumber($timespan->GetTotalMilliSeconds()/1000,$number_of_decimals).'"').'</abbr>'; }
	public static function AbbrDateTime( XDateTime $date_time = null , $default_value = ''){ return $date_time===null ? $default_value : '<abbr s="'.$date_time->Format('YmdHis').'" title="'. new XmlAttr(static::FormatTimeSpanSince($date_time)."\n".oxy::FormatTimeZone(Oxygen::GetCurrentTimeZone())).'">'.new Html(static::FormatDateTime($date_time)).'</abbr>'; }
	public static function AbbrDateTime24( XDateTime $date_time = null , $default_value = ''){ return $date_time===null ? $default_value : '<abbr s="'.$date_time->Format('YmdHis').'" title="'. new XmlAttr(static::FormatTimeSpanSince($date_time)."\n".oxy::FormatTimeZone(Oxygen::GetCurrentTimeZone())).'">'.new Html(static::FormatDateTime24($date_time)).'</abbr>';	}
	public static function AbbrDaysSince( XDateTime $date_time = null , $default_value = '' ){ if ($date_time===null) return $default_value; /** @var $diff XTimeSpan */ return '<span style="display:none;">'. sprintf('%020d',XDateTime::Now()->Diff( $date_time )->GetTotalMilliSeconds()) .'</span><abbr title="'. new XmlAttr(static::FormatDateTime($date_time).' - '.static::FormatTimeSpanSince($date_time)).'">'.static::FormatDateSpanSince($date_time).'</abbr>'; }
	public static function AbbrDate( XDateTime $date_time = null ){ return $date_time===null ? null : '<abbr s="'.$date_time->Format('YmdHis').'" title="'.new XmlAttr($date_time instanceof XDate ? static::FormatDate($date_time) . "\n" .static::FormatDateSpanSince($date_time) : static::FormatDateTime($date_time) . "\n" . static::FormatTimeSpanSince($date_time) ."\n".oxy::FormatTimeZone(Oxygen::GetCurrentTimeZone()) ).'">'.new Html(static::FormatDate($date_time)).'</abbr>'; }
	public static function AbbrByteRate( $byterate ){ return $byterate === null ? '' : ('<span style="display:none;">'.sprintf('%030d',$byterate).'</span><abbr title="'. new XmlAttr(Language::FormatNumber($byterate).' '.oxy::txtUnit_byte().'/'.oxy::txtUnit_sec()).'" class="nowrap">'.Language::FormatBytes($byterate).'/'.oxy::txtUnit_sec().'</abbr>'); }
	public static function AbbrByte( $bytes ){ return $bytes === null ? '' : ('<span style="display:none;">'.sprintf('%030d',$bytes).'</span><abbr title="'. new XmlAttr(Language::FormatNumber($bytes).' '.oxy::txtUnit_byte()).'" class="nowrap">'.Language::FormatBytes($bytes).'</abbr>'); }



	//
	//
	// Interface
	//
	//
	/** @return LoginControl */
	public static function GetLoginControl(){ return new LoginControl(); }

	/** @return array|null */
	public static function GetFavoriteColors(array $append = array(), array $remove = array()){ return null; }


	public static function GetErrorPageHead(){ return ''; }
	public static function GetErrorPageFoot(){ return ''; }

}
Oxygen::RegisterResourceManager('oxy','_oxy');
