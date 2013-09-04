<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
abstract class MJaxFormPostData{
    const ACTION = 'action';
    const CONTROL_ID = 'control_id';
    const EVENT = 'event';
    const MJaxForm__FormState = 'MJaxForm__FormState';
}
abstract class MJaxFormAction{
    const CONTROL_EVENT = 'control_event';
    const CHANGE_PAGE = 'change_page';
}
abstract class MJaxEventPostData{
    const KEYCODE = 'keyCode';
}
abstract class MJaxTransition{
    const NONE = 'None';
    const SLIDE = 'Slide';
    const FADE = 'Fade';
    const SET_VALUE = 'SetValue';
}
abstract class MJaxCallType {
	const Server = 'Server';
	const Ajax = 'Ajax';
	const None = 'None';
}
abstract class MJaxAssetMode{
	const WWW = 'www';
	const MOBILE = 'mobile';
	const CUSTOM = 'custom';
}
abstract class MJaxTextMode{
	const Password = 'password';
	const SingleLine = 'text';
	const Text = 'text';
	const MultiLine = 'MultiLine';
	const Color = 'color';
	const Date = 'date';
	const DateTime = 'datetime';
	const DateTimeLocal = 'datetime-local';
	const Email = 'email';
	const Month = 'month';
	const Number = 'number';
	const Range = 'range';
	const Search = 'search';
	const Phone = 'tel';
	const Time = 'time';
	const Url = 'url';
	const Week = 'week';
    const Hidden = 'hidden';
}
abstract class MJaxResponseFormat{
	const JSON = 'json';
	const XML = 'xml';
	const HTML = 'html';
}
abstract class MJaxTableDataMode{
	const DATA_ENTITY = 'DATA_ENTITY';
	const MJAX_ROW = 'MJAX_ROW';
}
abstract class MJaxTableEditMode{
    const NONE = 'none';
    const INLINE = 'inline';
}
?>
