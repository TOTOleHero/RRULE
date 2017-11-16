// Working variables:
var CurrentPattern = 0;
var CurrentRange = 0;
var DayList = [];
var DayInstanceList = [];

function DayInstance(a,b)
{
	this.Instance=a;
	this.DayOfWeek=b;
	this.Equals=DayInstanceEquals;
	this.Description=DayInstanceDescription;
	this.DayName=DayInstanceDayName;
	this.toString=DayInstanceToString;
}

function DayInstanceEquals(c)
{
	return(this.Instance==c.Instance&&this.DayOfWeek==c.DayOfWeek);
}

function DayInstanceDescription()
{
	var d,e,f;
	if(this.Instance==0)
		return"Any "+LongDaysOfWeek[this.DayOfWeek];
	d=(this.Instance%100)*((this.Instance<0)?-1:1);
	if((d>=10&&d<=19)||d%10==0)
		e=4;
	else
		e=d%10;
	switch(e)
	{
	case 1:
		f="st";
		break;
	case 2:
		f="nd";
		break;
	case 3:
		f="rd";
		break;
	default:
		f="th";
		break;
	}
	if(this.Instance<0)
		return(this.Instance*-1).toString()+f+" "+LongDaysOfWeek[this.DayOfWeek]+" from end";
	return this.Instance.toString()+f+" "+LongDaysOfWeek[this.DayOfWeek];
}

function DayInstanceDayName()
{
	return LongDaysOfWeek[this.DayOfWeek];
}

function DayInstanceToString()
{
	if(this.Instance==0)
		return Weekdays[this.DayOfWeek];
	return this.Instance.toString()+Weekdays[this.DayOfWeek];
}

function GetControl(b)
{
	return Utilities_GetElementById(b);
}

function LoadByDayValues()
{
	var _e,_f,_g,_h;
	_e=this.GetControl("RP_AP_INSTLST");
	_f=_e.selectedIndex;
	while(_e.options.length!=0)
		_e.remove(0);
	if(this.GetControl("RP_AP_DAY").disabled=="")
		for(_g=0;_g<this.DayInstanceList.length;_g++)
		{
			_h=document.createElement("OPTION");
			_h.text=this.DayInstanceList[_g].Description();
			try
			{
				_e.add(_h,null);
			}
			catch(ex)
			{
				_e.add(_h);
			}
		}
	else
		for(_g=0;_g<this.DayList.length;_g++)
		{
			_h=document.createElement("OPTION");
			_h.text=this.DayList[_g].DayName();
			try
			{
				_e.add(_h,null);
			}
			catch(ex)
			{
				_e.add(_h);
			}
		}
		if(_e.options.length>0)
		{
			this.GetControl("RP_AP_DELINST").disabled=this.GetControl("RP_AP_CLRINST").disabled="";
			_e.selectedIndex=(_f<_e.options.length)?_f:_e.options.length-1;
		}
		else
			this.GetControl("RP_AP_DELINST").disabled=this.GetControl("RP_AP_CLRINST").disabled="disabled";
}

function SetInterval(c,d)
{
	this.SetFrequency(c);
	if(d<1)
		d=1;
	else if(d>999)
		d=999;
	switch(c)
	{
		case RecurrenceRuleConstants_HOURLY:
			this.GetControl("RP_HP_INT").value=d.toString();
			break;
		case RecurrenceRuleConstants_MINUTELY:
			this.GetControl("RP_MINP_INT").value=d.toString();
			break;
		case RecurrenceRuleConstants_SECONDLY:
			this.GetControl("RP_SP_INT").value=d.toString();
			break;
	}
}

function SetDailyInterval(d,e)
{
	this.SetFrequency(RecurrenceRuleConstants_DAILY);
	if(d<1)
		d=1;
	else if(d>999)
		d=999;
	this.GetControl("RP_DP_INT").value=d.toString();
	this.SetDailyCtlState(!e);
}

function SetWeeklyInterval(d,f)
{
	var _g;
	this.SetFrequency(RecurrenceRuleConstants_WEEKLY);
	if(d<1)
		d=1;
	else if(d>999)
		d=999;
	this.GetControl("RP_WP_INT").value=d.toString();
	for(_g=0;_g<7;_g++)
		this.GetControl("RP_WP_"+Weekdays[_g]).checked=false;
	for(_g=0;_g<f.length;_g++)
		this.GetControl("RP_WP_"+Weekdays[f[_g].DayOfWeek]).checked=true;
}

function SetMonthlyInterval(g,d,f,h)
{
	var _g,_i,_j=0;
	this.SetFrequency(RecurrenceRuleConstants_MONTHLY);
	if(g<1)
		g=1;
	else if(g>31)
		g=31;
	if(d<1)
		d=1;
	else if(d>999)
		d=999;
	this.GetControl("RP_MP_DAY").value=g.toString();
	this.GetControl("RP_MP_INT").value=d.toString();
	this.GetControl("RP_MP_NTHINT").value=d.toString();
	if(f.length==0)
		this.SetMonthlyCtlState(true);
	else
	{
		this.SetMonthlyCtlState(false);
		if(f.length==1)
		{
			if(f[0].Instance<1||f[0].Instance>4)
				this.GetControl("RP_MP_INST").selectedIndex=4;
			else
				this.GetControl("RP_MP_INST").selectedIndex=f[0].Instance-1;
			this.GetControl("RP_MP_DOW").selectedIndex=f[0].DayOfWeek;
		}
		else
		{
			if(h<1||h>5)
				this.GetControl("RP_MP_INST").selectedIndex=4;
			else
				this.GetControl("RP_MP_INST").selectedIndex=h-1;
			for(_g=0;_g<f.length;_g++)
				switch(f[_g].DayOfWeek)
				{
					case 0:
						_j|=1;
						break;
					case 1:
						_j|=2;
						break;
					case 2:
						_j|=4;
						break;
					case 3:
						_j|=8;
						break;
					case 4:
						_j|=16;
						break;
					case 5:
						_j|=32;
						break;
					case 6:
						_j|=64;
						break;
				}
			if(_j==127)
				_i=9;
			else if(_j==65)
				_i=8;
			else if(_j==62)
				_i=7;
			else
				_i=f[0].DayOfWeek;
			this.GetControl("RP_MP_DOW").selectedIndex=_i;
		}
	}
}

function SetYearlyInterval(month,day,interval,dayList,h)
{
	this.SetFrequency(RecurrenceRuleConstants_YEARLY);
	if(month<1)
		month=1;
	else if(month>12)
		month=12;
	if(day<1)
		day=1;
	else if(day>31)
		day=31;
	if(interval<1)
		interval=1;
	else if(interval>999)
		interval=999;
	this.GetControl("RP_YP_MON").selectedIndex=this.GetControl("RP_YP_INMON").selectedIndex=month-1;
	this.GetControl("RP_YP_DAY").value=day.toString();
	this.GetControl("RP_YP_INT").value=interval.toString();
	this.GetControl("RP_YP_NTHINT").value=interval.toString();
	if(dayList.length==0)
		this.SetYearlyCtlState(true);
	else
	{
		this.SetYearlyCtlState(false);
		if(dayList.length==1)
		{
			if(dayList[0].Instance<1||dayList[0].Instance>4)
				this.GetControl("RP_YP_INST").selectedIndex=4;
			else
				this.GetControl("RP_YP_INST").selectedIndex=dayList[0].Instance-1;
			this.GetControl("RP_YP_DOW").selectedIndex=dayList[0].DayOfWeek;
		}
		else
		{
			if(h<1||h>5)
				this.GetControl("RP_YP_INST").selectedIndex=4;
			else
				this.GetControl("RP_YP_INST").selectedIndex=h-1;
			var dowMask = 0;
			for(var i=0;i<dayList.length;i++)
				switch(dayList[i].DayOfWeek)
				{
					case 0:
						dowMask|=1;
						break;
					case 1:
						dowMask|=2;
						break;
					case 2:
						dowMask|=4;
						break;
					case 3:
						dowMask|=8;
						break;
					case 4:
						dowMask|=16;
						break;
					case 5:
						dowMask|=32;
						break;
					case 6:
						dowMask|=64;
						break;
				}
			var dow = 0;
			if(dowMask==127)
				dow=9;
			else if(dowMask==65)
				dow=8;
			else if(dowMask==62)
				dow=7;
			else
				dow=dayList[0].DayOfWeek;
			this.GetControl("RP_YP_DOW").selectedIndex=dow;
		}
	}
}

function SetAdvancedInterval(freq,interval,dayList,bymonos,bywkno,byyrday,bymoday,byhour,byminute,bysecond,bypos)
{
	this.GetControl("RP_AP_BYWKNO").value=
		this.GetControl("RP_AP_BYYRDAY").value=
		this.GetControl("RP_AP_BYMODAY").value=
		this.GetControl("RP_AP_BYHOUR").value=
		this.GetControl("RP_AP_BYMIN").value=
		this.GetControl("RP_AP_BYSEC").value=
		this.GetControl("RP_AP_BYPOS").value="";
	var bymonth=this.GetControl("RP_AP_MOS").options;
	for(var i=0;i<bymonth.length;i++)
		bymonth[i].selected=false;
	this.SetFrequency(freq);
	var advanced=this.GetControl("RP_CHK_ADV");
	if(!advanced.checked)
	{
		advanced.checked=true;
		this.SetAdvancedState();
		for(var i=0;i<bymonth.length;i++)
			bymonth[i].selected=false;
	}
	this.GetControl("RP_AP_INT").value=interval.toString();
	if(typeof(dayList)!="undefined")
	{
		if(typeof(bymonos)!="undefined"&&bymonos.length!=0)
			for(var i=0;i<bymonos.length;i++)
				bymonth[bymonos[i]-1].selected=true;
		if(typeof(bywkno)!="undefined")
		{
			this.GetControl("RP_AP_BYWKNO").value=bywkno;
			this.GetControl("RP_AP_BYYRDAY").value=byyrday;
			this.GetControl("RP_AP_BYMODAY").value=bymoday;
			this.GetControl("RP_AP_BYHOUR").value=byhour;
			this.GetControl("RP_AP_BYMIN").value=byminute;
			this.GetControl("RP_AP_BYSEC").value=bysecond;
			this.GetControl("RP_AP_BYPOS").value=bypos;
		}
		this.SetDayInstanceState();
		if(this.GetControl("RP_AP_DAY").disabled=="")
			this.DayInstanceList=dayList;
		else
			this.DayList=dayList;
		this.LoadByDayValues();
	}
}
/*
function SetRangeOfRecurrence(r,s,t,u,v)
{
	if(u<1)
		u=1;
	else if(u>999)
		u=999;
	this.GetControl("RP_RNG_WSD").selectedIndex=r;
//	this.GetControl("RP_RNG_COH").checked=s;
	this.GetControl("RP_RNG_ENDOCC").value=u;
	this.GetControl("RP_RNG_ENDDT").value=v;
	this.SetRangeCtlState(t);
}
*/
function SetFrequency(freq)
{
/* DRL Removed this to allow changing the frequency even if there are other errors.
	if(this.CurrentPattern!=freq&&!this.ValidateRecurrence())
	{
		freq=this.CurrentPattern;
		this.GetControl("RBL_RPAT_" + RecurrenceRuleConstants_NO_RECURRENCE).checked=(freq==RecurrenceRuleConstants_NO_RECURRENCE);
		this.GetControl("RBL_RPAT_" + RecurrenceRuleConstants_YEARLY).checked=(freq==RecurrenceRuleConstants_YEARLY);
		this.GetControl("RBL_RPAT_" + RecurrenceRuleConstants_MONTHLY).checked=(freq==RecurrenceRuleConstants_MONTHLY);
		this.GetControl("RBL_RPAT_" + RecurrenceRuleConstants_WEEKLY).checked=(freq==RecurrenceRuleConstants_WEEKLY);
		this.GetControl("RBL_RPAT_" + RecurrenceRuleConstants_DAILY).checked=(freq==RecurrenceRuleConstants_DAILY);
		this.GetControl("RBL_RPAT_" + RecurrenceRuleConstants_HOURLY).checked=(freq==RecurrenceRuleConstants_HOURLY);
		this.GetControl("RBL_RPAT_" + RecurrenceRuleConstants_MINUTELY).checked=(freq==RecurrenceRuleConstants_MINUTELY);
		this.GetControl("RBL_RPAT_" + RecurrenceRuleConstants_SECONDLY).checked=(freq==RecurrenceRuleConstants_SECONDLY);
		return;
	}
*/
	var _m=this.GetControl("RP_CHK_ADV").checked;
	this.GetControl("RBL_RPAT_" + RecurrenceRuleConstants_NO_RECURRENCE).checked=(freq==RecurrenceRuleConstants_NO_RECURRENCE);
	this.GetControl("RBL_RPAT_" + RecurrenceRuleConstants_YEARLY).checked=(freq==RecurrenceRuleConstants_YEARLY);
	this.GetControl("RBL_RPAT_" + RecurrenceRuleConstants_MONTHLY).checked=(freq==RecurrenceRuleConstants_MONTHLY);
	this.GetControl("RBL_RPAT_" + RecurrenceRuleConstants_WEEKLY).checked=(freq==RecurrenceRuleConstants_WEEKLY);
	this.GetControl("RBL_RPAT_" + RecurrenceRuleConstants_DAILY).checked=(freq==RecurrenceRuleConstants_DAILY);
	this.GetControl("RBL_RPAT_" + RecurrenceRuleConstants_HOURLY).checked=(freq==RecurrenceRuleConstants_HOURLY);
	this.GetControl("RBL_RPAT_" + RecurrenceRuleConstants_MINUTELY).checked=(freq==RecurrenceRuleConstants_MINUTELY);
	this.GetControl("RBL_RPAT_" + RecurrenceRuleConstants_SECONDLY).checked=(freq==RecurrenceRuleConstants_SECONDLY);
	this.CurrentPattern=freq;
	
	this.GetControl("RP_SPN_ADV").style.display=(freq!=RecurrenceRuleConstants_NO_RECURRENCE)?"inline":"none";
	this.GetControl("RP_RNG_TABLE").style.display=(freq!=RecurrenceRuleConstants_NO_RECURRENCE)?"inline":"none";
	
	if(_m && freq!=RecurrenceRuleConstants_NO_RECURRENCE)
	{
		this.GetControl("RP_ADV_PAT").style.display="inline";
		this.GetControl("RP_YEAR_PAT").style.display=
			this.GetControl("RP_MON_PAT").style.display=
			this.GetControl("RP_WEEK_PAT").style.display=
			this.GetControl("RP_DAY_PAT").style.display=
			this.GetControl("RP_HOUR_PAT").style.display=
			this.GetControl("RP_MIN_PAT").style.display=
			this.GetControl("RP_SEC_PAT").style.display="none";
		switch(freq)
		{
			case RecurrenceRuleConstants_YEARLY:
				this.GetControl("RP_AP_INTLBL").innerHTML="year(s)";
				break;
			case RecurrenceRuleConstants_MONTHLY:
				this.GetControl("RP_AP_INTLBL").innerHTML="month(s)";
				break;
			case RecurrenceRuleConstants_WEEKLY:
				this.GetControl("RP_AP_INTLBL").innerHTML="week(s)";
				break;
			case RecurrenceRuleConstants_DAILY:
				this.GetControl("RP_AP_INTLBL").innerHTML="day(s)";
				break;
			case RecurrenceRuleConstants_HOURLY:
				this.GetControl("RP_AP_INTLBL").innerHTML="hour(s)";
				break;
			case RecurrenceRuleConstants_MINUTELY:
				this.GetControl("RP_AP_INTLBL").innerHTML="minute(s)";
				break;
			case RecurrenceRuleConstants_SECONDLY:
			default:
				this.GetControl("RP_AP_INTLBL").innerHTML="second(s)";
				break;
		}
		if(freq==RecurrenceRuleConstants_YEARLY)
		{
			this.GetControl("RP_AP_BYWKNO").disabled="";
			this.GetControl("RP_AP_BYWKNO").className="EWS_RP_Input";
		}
		else
		{
			this.GetControl("RP_AP_BYWKNO").disabled="disabled";
			this.GetControl("RP_AP_BYWKNO").className="EWS_RP_Disabled";
		}
		if(freq<RecurrenceRuleConstants_WEEKLY)
			this.SetDayInstanceState();
		else
		{
			this.GetControl("RP_AP_DAY").disabled="disabled";
			this.GetControl("RP_AP_DAY").className="EWS_RP_Disabled";
			this.LoadByDayValues();
		}
	}
	else
	{
		this.GetControl("RP_ADV_PAT").style.display="none";
		this.GetControl("RP_YEAR_PAT").style.display=(freq==RecurrenceRuleConstants_YEARLY)?"inline":"none";
		this.GetControl("RP_MON_PAT").style.display=(freq==RecurrenceRuleConstants_MONTHLY)?"inline":"none";
		this.GetControl("RP_WEEK_PAT").style.display=(freq==RecurrenceRuleConstants_WEEKLY)?"inline":"none";
		this.GetControl("RP_DAY_PAT").style.display=(freq==RecurrenceRuleConstants_DAILY)?"inline":"none";
		this.GetControl("RP_HOUR_PAT").style.display=(freq==RecurrenceRuleConstants_HOURLY)?"inline":"none";
		this.GetControl("RP_MIN_PAT").style.display=(freq==RecurrenceRuleConstants_MINUTELY)?"inline":"none";
		this.GetControl("RP_SEC_PAT").style.display=(freq==RecurrenceRuleConstants_SECONDLY)?"inline":"none";
	}
}

function SetAdvancedState()
{
	var interval,advanced,_j,dayList,_n;
	var h,bymonth,j;
	advanced=this.GetControl("RP_CHK_ADV");
	advanced.checked=!advanced.checked;
/* DRL Removed this to allow changing the frequency even if there are other errors.
	if(!this.ValidateRecurrence())
		return false;
*/
	advanced.checked=!advanced.checked;
	if(!advanced.checked)
	{
		interval=parseInt(this.GetControl("RP_AP_INT").value,10);
		_n=this.GetControl("RP_AP_BYMODAY").value;
		_n=_n.split(/[,\-]/);
		if(_n.length==0)
			_n=1;
		else
		{
			_n=parseInt(_n[0],10);
			if(isNaN(_n))
				_n=1;
		}
		h=this.GetControl("RP_AP_BYPOS").value;
		if(substr(h, 0,1)!="-")
			h=h.split(/[,\-]/);
		else
			h=substr(h, 1).split(/,/);
		if(h.length==0)
			h=1;
		else
		{
			h=parseInt(h[0],10);
			if(isNaN(h))
				h=1;
		}
		if(this.GetControl("RP_AP_DAY").disabled=="")
			dayList=this.DayInstanceList;
		else
			dayList=this.DayList;
		switch(this.CurrentPattern)
		{
			case RecurrenceRuleConstants_YEARLY:
				bymonth=this.GetControl("RP_AP_MOS").options;
				var month;
				for(month=0;month<bymonth.length;month++)
					if(bymonth[month].selected)
						break;
				if(month>=bymonth.length)
					month=0;
				this.SetYearlyInterval(month+1,_n,interval,dayList,h);
				break;
			case RecurrenceRuleConstants_MONTHLY:
				this.SetMonthlyInterval(_n,interval,dayList,h);
				break;
			case RecurrenceRuleConstants_WEEKLY:
				this.SetWeeklyInterval(interval,this.DayList);
				break;
			case RecurrenceRuleConstants_DAILY:
				for(var i=0;i<dayList.length;i++)
					switch(dayList[i].DayOfWeek)
					{
						case 0:
							_j|=1;
							break;
						case 1:
							_j|=2;
							break;
						case 2:
							_j|=4;
							break;
						case 3:
							_j|=8;
							break;
						case 4:
							_j|=16;
							break;
						case 5:
							_j|=32;
							break;
						case 6:
							_j|=64;
							break;
					}
				if(_j==62)
					this.SetDailyInterval(1,true);
				else
					this.SetDailyInterval(interval,false);
				break;
			case RecurrenceRuleConstants_HOURLY:
				this.SetInterval(RecurrenceRuleConstants_HOURLY,interval);
				break;
			case RecurrenceRuleConstants_MINUTELY:
				this.SetInterval(RecurrenceRuleConstants_MINUTELY,interval);
				break;
			case RecurrenceRuleConstants_SECONDLY:
			default:
				this.SetInterval(RecurrenceRuleConstants_SECONDLY,interval);
				break;
		}
		this.DayInstanceList=[];
		this.DayList=[];
	}
	else
	{
		switch(this.CurrentPattern)
		{
			case RecurrenceRuleConstants_YEARLY:
				if(this.GetControl("RP_YP_XOFY").checked)
				{
					j=[this.GetControl("RP_YP_MON").selectedIndex+1];
					_n=this.GetControl("RP_YP_DAY").value;
					interval=parseInt(this.GetControl("RP_YP_INT").value,10);
					dayList=[];
					h="";
				}
				else
				{
					_n="";
					j=[];
					j[0]=this.GetControl("RP_YP_INMON").selectedIndex+1;
					interval=parseInt(this.GetControl("RP_YP_NTHINT").value,10);
					h=this.GetControl("RP_YP_INST").selectedIndex+1;
					if(h>4)
						h=-1;
					var dow=this.GetControl("RP_YP_DOW").selectedIndex;
					switch(dow)
					{
						case 7:
							h=h.toString();
							dayList=[new DayInstance(0,1),new DayInstance(0,2),new DayInstance(0,3),new DayInstance(0,4),new DayInstance(0,5)];
							break;
						case 8:
							h=h.toString();
							dayList=[new DayInstance(0,0),new DayInstance(0,6)];
							break;
						case 9:
							h=h.toString();
							dayList=[new DayInstance(0,0),new DayInstance(0,1),new DayInstance(0,2),new DayInstance(0,3),new DayInstance(0,4),new DayInstance(0,5),new DayInstance(0,6)];
							break;
						default:
							dayList=[new DayInstance(h,dow)];
							h="";
							break;
					}
				}
				this.SetAdvancedInterval(RecurrenceRuleConstants_YEARLY,interval,dayList,j,"","",_n,"","","",h);
				break;
			case RecurrenceRuleConstants_MONTHLY:
				if(this.GetControl("RP_MP_XOFY").checked)
				{
					_n=this.GetControl("RP_MP_DAY").value;
					interval=parseInt(this.GetControl("RP_MP_INT").value,10);
					dayList=[];
					h="";
				}
				else
				{
					_n="";
					interval=parseInt(this.GetControl("RP_MP_NTHINT").value,10);
					h=this.GetControl("RP_MP_INST").selectedIndex+1;
					if(h>4)
						h=-1;
					var dow=this.GetControl("RP_MP_DOW").selectedIndex;
					switch(dow)
					{
						case 7:
							h=h.toString();
							dayList=[new DayInstance(0,1),new DayInstance(0,2),new DayInstance(0,3),new DayInstance(0,4),new DayInstance(0,5)];
							break;
						case 8:
							h=h.toString();
							dayList=[new DayInstance(0,0),new DayInstance(0,6)];
							break;
						case 9:
							h=h.toString();
							dayList=[new DayInstance(0,0),new DayInstance(0,1),new DayInstance(0,2),new DayInstance(0,3),new DayInstance(0,4),new DayInstance(0,5),new DayInstance(0,6)];
							break;
						default:
							dayList=[new DayInstance(h,dow)];
							h="";
							break;
					}
				}
				this.SetAdvancedInterval(RecurrenceRuleConstants_MONTHLY,interval,dayList,[],"","",_n,"","","",h);
				break;
			case RecurrenceRuleConstants_WEEKLY:
				dayList=[];
				for(var dow=0;dow<7;dow++)
					if(this.GetControl("RP_WP_"+Weekdays[dow]).checked)
						dayList[dayList.length]=new DayInstance(0,dow);
				this.SetAdvancedInterval(RecurrenceRuleConstants_WEEKLY,parseInt(this.GetControl("RP_WP_INT").value,10),dayList);
				break;
			case RecurrenceRuleConstants_DAILY:
				if(this.GetControl("RP_DP_INST").checked)
					this.SetAdvancedInterval(RecurrenceRuleConstants_DAILY,parseInt(this.GetControl("RP_DP_INT").value,10));
				else
				{
					this.SetAdvancedInterval(RecurrenceRuleConstants_DAILY,1,[new DayInstance(0,1),new DayInstance(0,2),new DayInstance(0,3),new DayInstance(0,4),new DayInstance(0,5)]);
				}
				break;
			case RecurrenceRuleConstants_HOURLY:
				this.SetAdvancedInterval(RecurrenceRuleConstants_HOURLY,parseInt(this.GetControl("RP_HP_INT").value,10));
				break;
			case RecurrenceRuleConstants_MINUTELY:
				this.SetAdvancedInterval(RecurrenceRuleConstants_MINUTELY,parseInt(this.GetControl("RP_MINP_INT").value,10));
				break;
			case RecurrenceRuleConstants_SECONDLY:
			default:
				this.SetAdvancedInterval(RecurrenceRuleConstants_SECONDLY,parseInt(this.GetControl("RP_SP_INT").value,10));
				break;
		}
	}
	this.SetFrequency(this.CurrentPattern);
}

function SetRangeCtlState(t)
{
	this.GetControl("RP_RNG_OCCVAL").style.display="none";
	this.GetControl("RP_RNG_DTVAL").style.display="none";
	switch(t)
	{
		case 0:
			this.GetControl("RP_RNG_NONE").checked=true;
			this.GetControl("RP_RNG_OCC").checked=this.GetControl("RP_RNG_DATE").checked=false;
			this.GetControl("RP_RNG_ENDOCC").disabled=this.GetControl("RP_RNG_ENDDT").disabled="disabled";
			this.GetControl("RP_RNG_ENDOCC").className=this.GetControl("RP_RNG_ENDDT").className="EWS_RP_Disabled";
			break;
		case 1:
			this.GetControl("RP_RNG_OCC").checked=true;
			this.GetControl("RP_RNG_NONE").checked=this.GetControl("RP_RNG_DATE").checked=false;
			this.GetControl("RP_RNG_ENDOCC").disabled="";
			this.GetControl("RP_RNG_ENDDT").disabled="disabled";
			this.GetControl("RP_RNG_ENDOCC").className="EWS_RP_Input";
			this.GetControl("RP_RNG_ENDDT").className="EWS_RP_Disabled";
			break;
		default:
			this.GetControl("RP_RNG_DATE").checked=true;
			this.GetControl("RP_RNG_NONE").checked=this.GetControl("RP_RNG_OCC").checked=false;
			this.GetControl("RP_RNG_ENDOCC").disabled="disabled";
			this.GetControl("RP_RNG_ENDDT").disabled="";
			this.GetControl("RP_RNG_ENDOCC").className="EWS_RP_Disabled";
			this.GetControl("RP_RNG_ENDDT").className="EWS_RP_Input";
			break;
	}
	this.CurrentRange=t;
}

function SetDailyCtlState(y)
{
	this.GetControl("RP_DP_VAL").style.display="none";
	if(y)
	{
		this.GetControl("RP_DP_INST").checked=true;
		this.GetControl("RP_DP_INT").disabled="";
		this.GetControl("RP_DP_WD").checked=false;
		this.GetControl("RP_DP_INT").className="EWS_RP_Input";
	}
	else
	{
		this.GetControl("RP_DP_INST").checked=false;
		this.GetControl("RP_DP_INT").disabled="disabled";
		this.GetControl("RP_DP_WD").checked=true;
		this.GetControl("RP_DP_INT").className="EWS_RP_Disabled";
	}
}

function SetMonthlyCtlState(z)
{
	this.GetControl("RP_MP_DAY_VAL").style.display="none";
	this.GetControl("RP_MP_INT_VAL").style.display="none";
	if(z)
	{
		this.GetControl("RP_MP_XOFY").checked=true;
		this.GetControl("RP_MP_DAY").disabled=this.GetControl("RP_MP_INT").disabled="";
		this.GetControl("RP_MP_NTH").checked=false;
		this.GetControl("RP_MP_INST").disabled=this.GetControl("RP_MP_DOW").disabled=this.GetControl("RP_MP_NTHINT").disabled="disabled";
		this.GetControl("RP_MP_DAY").className=this.GetControl("RP_MP_INT").className="EWS_RP_Input";
		this.GetControl("RP_MP_INST").className=this.GetControl("RP_MP_DOW").className=this.GetControl("RP_MP_NTHINT").className="EWS_RP_Disabled";
	}
	else
	{
		this.GetControl("RP_MP_XOFY").checked=false;
		this.GetControl("RP_MP_DAY").disabled=this.GetControl("RP_MP_INT").disabled="disabled";
		this.GetControl("RP_MP_NTH").checked=true;
		this.GetControl("RP_MP_INST").disabled=this.GetControl("RP_MP_DOW").disabled=this.GetControl("RP_MP_NTHINT").disabled="";
		this.GetControl("RP_MP_DAY").className=this.GetControl("RP_MP_INT").className="EWS_RP_Disabled";
		this.GetControl("RP_MP_INST").className="EWS_RP_Input";
		this.GetControl("RP_MP_DOW").className=this.GetControl("RP_MP_NTHINT").className="EWS_RP_Select";
	}
}

function SetYearlyCtlState(z)
{
	this.GetControl("RP_YP_DAY_VAL").style.display="none";
	this.GetControl("RP_YP_INT_VAL").style.display="none";
	if(z)
	{
		this.GetControl("RP_YP_XOFY").checked=true;
		this.GetControl("RP_YP_MON").disabled=this.GetControl("RP_YP_DAY").disabled=this.GetControl("RP_YP_INT").disabled="";
		this.GetControl("RP_YP_NTH").checked=false;
		this.GetControl("RP_YP_INST").disabled=this.GetControl("RP_YP_DOW").disabled=this.GetControl("RP_YP_INMON").disabled=this.GetControl("RP_YP_NTHINT").disabled="disabled";
		this.GetControl("RP_YP_MON").className=this.GetControl("RP_YP_DAY").className=this.GetControl("RP_YP_INT").className="EWS_RP_Input";
		this.GetControl("RP_YP_INST").className=this.GetControl("RP_YP_DOW").className=this.GetControl("RP_YP_INMON").className=this.GetControl("RP_YP_NTHINT").className="EWS_RP_Disabled";
	}
	else
	{
		this.GetControl("RP_YP_XOFY").checked=false;
		this.GetControl("RP_YP_MON").disabled=this.GetControl("RP_YP_DAY").disabled=this.GetControl("RP_YP_INT").disabled="disabled";
		this.GetControl("RP_YP_NTH").checked=true;
		this.GetControl("RP_YP_INST").disabled=this.GetControl("RP_YP_DOW").disabled=this.GetControl("RP_YP_INMON").disabled=this.GetControl("RP_YP_NTHINT").disabled="";
		this.GetControl("RP_YP_MON").className=this.GetControl("RP_YP_DAY").className=this.GetControl("RP_YP_INT").className="EWS_RP_Disabled";
		this.GetControl("RP_YP_INST").className="EWS_RP_Input";
		this.GetControl("RP_YP_DOW").className=this.GetControl("RP_YP_INMON").className=this.GetControl("RP_YP_NTHINT").className="EWS_RP_Select";
	}
}

function SetDayInstanceState()
{
	var _o,_p,_q,_r,_g,_s;
	_o=this.GetControl("RP_AP_DAY");
	_p=_q=(_o.disabled=="");
	if(this.CurrentPattern==RecurrenceRuleConstants_YEARLY)
	{
		_r=this.GetControl("RP_AP_MOS").options;
		_s=_r.length;
		for(_g=0;_g<_s;_g++)
			if(_r[_g].selected)
				break;
		if(_g<_s)
			_p=(this.GetControl("RP_AP_BYMODAY").value.length==0);
		else
			_p=(this.GetControl("RP_AP_BYWKNO").value.length==0);
	}
	else if(this.CurrentPattern==RecurrenceRuleConstants_MONTHLY)
		_p=(this.GetControl("RP_AP_BYMODAY").value.length==0);
	if(_q!=_p)
	{
		_o.disabled=(_p)?"":"disabled";
		_o.className=(_p)?"EWS_RP_Input":"EWS_RP_Disabled";
	}
	this.LoadByDayValues();
}

function AddDayInstance()
{
	var _t,_e,_h,di,_g;
	_t=this.GetControl("RP_AP_DAY");
	if(_t.disabled==""&&!this.ValidateNumeric("RP_AP_DAY","RP_AP_DAYVAL",-53,53))
		return;
	_e=this.GetControl("RP_AP_INSTLST");
	_h=document.createElement("OPTION");
	if(_t.disabled=="")
	{
		di=new DayInstance(parseInt(_t.value,10),this.GetControl("RP_AP_DOW").selectedIndex);
		if(this.DayInstanceList.length==0)
			this.DayInstanceList[0]=di;
		else
		{
			for(_g=0;_g<this.DayInstanceList.length;_g++)
				if(this.DayInstanceList[_g].Instance==di.Instance&&this.DayInstanceList[_g].DayOfWeek==di.DayOfWeek)
					break;
			if(_g<this.DayInstanceList.length)
				return;
			this.DayInstanceList[this.DayInstanceList.length]=di;
		}
		_h.text=di.Description();
	}
	else
	{
		di=new DayInstance(0,this.GetControl("RP_AP_DOW").selectedIndex);
		if(this.DayList.length==0)
			this.DayList[0]=di;
		else
		{
			for(_g=0;_g<this.DayList.length;_g++)
				if(this.DayList[_g].DayOfWeek==di.DayOfWeek)
					break;
			if(_g<this.DayList.length)
				return;
			this.DayList[this.DayList.length]=di;
		}
		_h.text=di.DayName();
	}
	try
	{
		_e.add(_h,null);
	}
	catch(ex)
	{
		_e.add(_h);
	}
	this.GetControl("RP_AP_DELINST").disabled=this.GetControl("RP_AP_CLRINST").disabled="";
}

function RemoveDayInstance()
{
	var _e,_f;
	this.GetControl("RP_AP_DAYVAL").style.display="none";
	_e=this.GetControl("RP_AP_INSTLST");
	_f=_e.selectedIndex;
	if(_f==-1)
		_e.selectedIndex=0;
	else
	{
		if(this.GetControl("RP_AP_DAY").disabled=="")
			this.DayInstanceList.splice(_f,1);
		else
			this.DayList.splice(_f,1);
		_e.remove(_f);
		if(_f<_e.options.length)
			_e.selectedIndex=_f;
		else if(_e.options.length!=0)
			_e.selectedIndex=_e.options.length-1;
		else
			this.GetControl("RP_AP_DELINST").disabled=this.GetControl("RP_AP_CLRINST").disabled="disabled";
	}
}

function ClearDayInstances()
{
	var _e;
	this.GetControl("RP_AP_DAYVAL").style.display="none";
	_e=this.GetControl("RP_AP_INSTLST");
	while(_e.options.length!=0)
		_e.remove(0);
	this.DayList=[];
	this.DayInstanceList=[];
	_e.selectedIndex=-1;
	this.GetControl("RP_AP_DELINST").disabled=this.GetControl("RP_AP_CLRINST").disabled="disabled";
}

function ValidateRecurrence()
{
	var i,_u,bymonth,_g,_v=true;//,c="";
	if(this.GetControl("RP_CHK_ADV").checked)
	{
		_v=this.ValidateNumeric("RP_AP_INT","RP_AP_INTVAL",1,999);
//		c="1\xFF"+this.CurrentPattern.toString()+"\xFF";
//		c+=this.GetControl("RP_AP_INT").value+"\xFF";
		bymonth=this.GetControl("RP_AP_MOS").options;
//		for(_g=0;_g<bymonth.length;_g++)
//			c+=bymonth[_g].selected?"1":"0";
//		c+="\xFF";
//		if(this.GetControl("RP_AP_DAY").disabled=="")
//			for(_g=0;_g<this.DayInstanceList.length;_g++)
//			{
//				if(_g!=0)
//					c+=",";
//				c+=this.DayInstanceList[_g].toString();
//			}
//		else
//			for(_g=0;_g<this.DayList.length;_g++)
//			{
//				if(_g!=0)
//					c+=",";
//				c+=this.DayList[_g].toString();
//			}
//		c+="\xFF";
//		if(this.GetControl("RP_AP_BYWKNO").disabled=="")
//			c+=this.GetControl("RP_AP_BYWKNO").value+"\xFF";
//		else
//			c+="\xFF";
//		c+=this.GetControl("RP_AP_BYYRDAY").value+"\xFF";
//		c+=this.GetControl("RP_AP_BYMODAY").value+"\xFF";
//		c+=this.GetControl("RP_AP_BYHOUR").value+"\xFF";
//		c+=this.GetControl("RP_AP_BYMIN").value+"\xFF";
//		c+=this.GetControl("RP_AP_BYSEC").value+"\xFF";
//		c+=this.GetControl("RP_AP_BYPOS").value+"\xFF";
	}
	else
	{
//		c="0\xFF"+this.CurrentPattern.toString()+"\xFF";
		switch(this.CurrentPattern)
		{
			case RecurrenceRuleConstants_YEARLY:
				i=this.GetControl("RP_YP_MON").selectedIndex;
				_u=new Date(2003,i+1,1);
				_u=new Date(_u.getTime()-86400000);
				_u=_u.getDate();
				this.GetControl("RP_YP_MAXDAYS").innerHTML=_u;
				if(this.GetControl("RP_YP_XOFY").checked)
				{
					if(!this.ValidateNumeric("RP_YP_DAY","RP_YP_DAY_VAL",1,_u))
						_v=false;
					if(!this.ValidateNumeric("RP_YP_INT","RP_YP_INT_VAL",1,999))
						_v=false;
//					c+="0\xFF";
//					c+=this.GetControl("RP_YP_MON").selectedIndex.toString()+"\xFF";
//					c+=this.GetControl("RP_YP_DAY").value+"\xFF";
//					c+=this.GetControl("RP_YP_INT").value+"\xFF";
				}
				else
				{
					_v=this.ValidateNumeric("RP_YP_NTHINT","RP_YP_INT_VAL",1,999);
//					c+="1\xFF";
//					c+=this.GetControl("RP_YP_INST").selectedIndex.toString()+"\xFF";
//					c+=this.GetControl("RP_YP_DOW").selectedIndex.toString()+"\xFF";
//					c+=this.GetControl("RP_YP_INMON").selectedIndex.toString()+"\xFF";
//					c+=this.GetControl("RP_YP_NTHINT").value+"\xFF";
				}
				break;
			case RecurrenceRuleConstants_MONTHLY:
				if(this.GetControl("RP_MP_XOFY").checked)
				{
					if(!this.ValidateNumeric("RP_MP_DAY","RP_MP_DAY_VAL",1,31))
						_v=false;
					if(!this.ValidateNumeric("RP_MP_INT","RP_MP_INT_VAL",1,999))
						_v=false;
//					c+="0\xFF";
//					c+=this.GetControl("RP_MP_DAY").value+"\xFF";
//					c+=this.GetControl("RP_MP_INT").value+"\xFF";
				}
				else
				{
					_v=this.ValidateNumeric("RP_MP_NTHINT","RP_MP_INT_VAL",1,999);
//					c+="1\xFF";
//					c+=this.GetControl("RP_MP_INST").selectedIndex.toString()+"\xFF";
//					c+=this.GetControl("RP_MP_DOW").selectedIndex.toString()+"\xFF";
//					c+=this.GetControl("RP_MP_NTHINT").value+"\xFF";
				}
				break;
			case RecurrenceRuleConstants_WEEKLY:
				_v=this.ValidateNumeric("RP_WP_INT","RP_WP_VAL",1,999);
//				c+=this.GetControl("RP_WP_INT").value+"\xFF";
//				c+=((this.GetControl("RP_WP_SU").checked)?"1":"0");
//				c+=((this.GetControl("RP_WP_MO").checked)?"1":"0");
//				c+=((this.GetControl("RP_WP_TU").checked)?"1":"0");
//				c+=((this.GetControl("RP_WP_WE").checked)?"1":"0");
//				c+=((this.GetControl("RP_WP_TH").checked)?"1":"0");
//				c+=((this.GetControl("RP_WP_FR").checked)?"1":"0");
//				c+=((this.GetControl("RP_WP_SA").checked)?"1":"0");
//				c+="\xFF";
				break;
			case RecurrenceRuleConstants_DAILY:
				if(this.GetControl("RP_DP_INST").checked)
				{
					_v=this.ValidateNumeric("RP_DP_INT","RP_DP_VAL",1,999);
//					c+="0\xFF"+this.GetControl("RP_DP_INT").value+"\xFF";
				}
//				else
//					c+="1\xFF";
				break;
			case RecurrenceRuleConstants_HOURLY:
				_v=this.ValidateNumeric("RP_HP_INT","RP_HP_VAL",1,999);
//				c+=this.GetControl("RP_HP_INT").value+"\xFF";
				break;
			case RecurrenceRuleConstants_MINUTELY:
				_v=this.ValidateNumeric("RP_MINP_INT","RP_MINP_VAL",1,999);
//				c+=this.GetControl("RP_MINP_INT").value+"\xFF";
				break;
			case RecurrenceRuleConstants_SECONDLY:
			default:
				_v=this.ValidateNumeric("RP_SP_INT","RP_SP_VAL",1,999);
//				c+=this.GetControl("RP_SP_INT").value+"\xFF";
				break;
		}
	}
//	c+=this.GetControl("RP_RNG_WSD").selectedIndex.toString()+"\xFF";
//	c+=((this.GetControl("RP_RNG_COH").checked)?"1":"0")+"\xFF";
//	c+=this.CurrentRange.toString()+"\xFF";
	switch(this.CurrentRange)
	{
		case 1:
			if(!this.ValidateNumeric("RP_RNG_ENDOCC","RP_RNG_OCCVAL",1,999))
				_v=false;
//			else
//				c+=this.GetControl("RP_RNG_ENDOCC").value;
			break;
		case 2:
			if(!this.ValidateEndDate())
			{
				_v=false;
				this.GetControl("RP_RNG_DTVAL").style.display="inline";
			}
			else
			{
				this.GetControl("RP_RNG_DTVAL").style.display="none";
//				c+=this.GetControl("RP_RNG_ENDDT").value;
			}
			break;
		default:
			break;
	}
//	if(!_v)
//		c="";
//	document.getElementsByName("Recurrence_Value")[0].value=c;
	return _v;
}

function ValidateNumeric(valueId,errorId,min,max)
{
	var result=true;
	var valueCtrl=this.GetControl(valueId);
	var errorCtrl=this.GetControl(errorId);
	var valueInt,value=valueCtrl.value.replace(/\s*/g,"");
	valueCtrl.value=value;
	if(value==""||!/^\-?[0-9]+$/.test(value))
		valueInt=-32767;
	else
		valueInt=parseInt(value,10);
	if(valueInt<min||valueInt>max)
	{
		errorCtrl.style.display="inline";
		result=false;
	}
	else
		errorCtrl.style.display="none";
	return result;
}

function ValidateEndDate()
{
	var _aa,v,_ab,_ac,i,day,_ad,sep;
	_aa=this.GetControl("RP_RNG_ENDDT");
	_ab=strtolower(_aa.value).replace(/[\s*]/g,"");
	v=Date.parse(_ab);
	if(isNaN(v))
		return false;
	_ac=_ab.split(/[^0-9]/);
	if(_ac.length!=3)
		return true;
	sep=_ab.charAt(_ac[0].length);
	v=new Date(v);
	i=parseInt(_ac[0],10);
	day=parseInt(_ac[1],10);
	if(i!=v.getMonth()+1&&i!=v.getDate())
		return false;
	if(day!=v.getMonth()+1&&day!=v.getDate())
		return false;
	if(_ac[0].length<2)
		_ac[0]="0"+_ac[0];
	if(_ac[1].length<2)
		_ac[1]="0"+_ac[1];
	_ad=parseInt(_ac[2],10);
	if(_ad<1000)
		if(_ad<30||_ad>99)
			_ad+=2000;
		else
			_ad+=1900;
	if(_ad!=v.getYear())
		return false;
	_aa.value=_ac[0]+sep+_ac[1]+sep+_ad.toString();
	return true;
}
