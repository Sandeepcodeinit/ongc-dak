@php
@endphp
<div class="row routeUrl" data-routename="{{ route($routename) }}" data-route="{{ $routename }}">
    <div class="col-sm-3">
        <div class="dataTables_length">
            Show<br>
            <select class="custom-select custom-select-sm form-control form-control-sm listLength" style="width: auto;" onchange="searchRecords(this);">
                <option value="10" {{ ($list_length == 10)?'selected':'' }}>10</option>
                <option value="50" {{ ($list_length == 50)?'selected':'' }}>50</option>
                <option value="100" {{ ($list_length == 100)?'selected':'' }}>100</option>
                <option value="500" {{ ($list_length == 500)?'selected':'' }}>500</option>
                <option value="1000" {{ ($list_length == 1000)?'selected':'' }}>1000</option>
                <option value="2000" {{ ($list_length == 2000)?'selected':'' }}>2000</option>
            </select>
            Entries
        </div>
    </div>
    @switch($routename)
    @case('user_list.index')
        <div class="col-sm-3">   
            Work Center<br>
            <select class="select2 custom-select custom-select-sm form-control form-control-sm listWc" style="width: 100%;" onchange="searchRecords(this);" data-wc_code="{{ $wc_code }}">
                <option value="">All Work Center</option>
                @php
                $addressList = addressToList();
                @endphp
                @foreach($addressList as $row)
                <option value="{{ $row->adto_id }}" {{ $row->adto_id == $wc_code?'selected':'' }}>{{ $row->addressto }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-sm-3 otherPage">  
            Role Type<br>
            <select class="custom-select custom-select-sm form-control form-control-sm listRole" style="width: 100%;" onchange="searchRecords(this);" data-role_code="{{ $role_code }}">
                <option value="">All Role Type</option>
                @php
                $roleList = roleType();
                @endphp
                @foreach($roleList as $row)
                <option value="{{ $row->role_id }}" {{ $row->role_id == $role_code?'selected':'' }}>{{ $row->role_type }}</option>
                @endforeach
            </select>
        </div>
    @break
    @case('tagthem.index')
        <div class="col-sm-3">
            Type<br>
            <select class="select custom-select custom-select-sm form-control form-control-sm listType" style="width: auto;" onchange="searchRecords(this);">
                <option value="">All</option>
                <option value="THEAM" {{ $tag_type == "THEAM"?'selected':'' }}>Thematic</option>
                <option value="TAGS" {{ $tag_type == "TAGS"?'selected':'' }}>Tags</option>
            </select>
        </div>
        <div class="col-sm-3 otherPage"></div>
    @break
    @case('vip_commitee.list')
        <div class="col-sm-6">
            Committee <br>
            <select class="select2 custom-select custom-select-sm form-control form-control-sm listCommittee" style="width: 100%;" onchange="searchRecords(this);" data-committee_code="{{ $committee_code }}">
                <option value="">All Committee</option>
                @php
                $committeeListList = committeeList('List');
                @endphp
                @foreach($committeeListList as $row)
                <option value="{{ $row->id }}" {{ $row->id == $committee_code?'selected':'' }}>{{ $row->committee_name }}</option>
                @endforeach
            </select>
        </div>
    @break
    @default
        <div class="col-sm-3"></div>
        <div class="col-sm-3 otherPage"></div>
    @endswitch

    <div class="col-sm-3">
        <div class="dataTables_filter float-right">
                Search &nbsp;<br>
            <div class="btn-group">
                <input type="search" class="form-control form-control-sm listSearch" data-id="btnSearchList" value="{{ @$list_search }}" placeholder=" Search here..." style="display: inline; width: auto;">
                <button type="button" class="btn btn-sm btn-success btnSearchList" title="Search" onclick="searchRecords(this);"><i class="fa fa-search"></i></button>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
function searchRecords(thiss) {
    var routeUrl = $('.routeUrl').attr('data-routename');
    var routeNam = $('.routeUrl').attr('data-route');
    //console.log(routeUrl); return false;
    var listLength = $('.listLength').val();
    var listSearch = $('.listSearch').val();
    var listEventUrl = '';
    switch (routeNam) {
        case 'user_list.index':
            var listEventUrl = '?length='+listLength+'&search='+listSearch;
            var listRole = $('.listRole').val();
            if(parseInt(listRole) <= 0 || listRole == ''){
                listRole = '';
            }
            listEventUrl += '&role_code='+listRole;

            var listWc = $('.listWc').val();
            if(parseInt(listWc) <= 0 || listWc == ''){
                listWc = '';
            }
            listEventUrl += '&wc_code='+listWc;

            location = routeUrl+listEventUrl;
            //console.log(location); return false;
        break;
        case 'tagthem.index':
            var listEventUrl = '?length='+listLength+'&search='+listSearch;
            var listType = $('.listType').val();
            if(listType == ''){
                listType = '';
            }
            listEventUrl += '&tag_type='+listType;
            location = routeUrl+listEventUrl;
        break;
        case 'special.districts':
            location = routeUrl+"?length="+listLength+"&search="+listSearch;
        break;
        case 'vip_commitee.list':
            var listEventUrl = '?length='+listLength+'&search='+listSearch;
            var committee_code = $('.listCommittee').val();
            if(committee_code == ''){
                committee_code = '';
            }
            listEventUrl += '&committee_code='+committee_code;
            location = routeUrl+listEventUrl;
        break;
        case 'legacy.dashboard':
            var listEventUrl = '?length='+listLength+'&search='+listSearch;
            location = routeUrl+listEventUrl;
        break;
        default:
            //console.log(routeUrl+"/"+listLength+"/"+listSearch);
            location = routeUrl+"/"+listLength+"/"+listSearch;
        break;
    }
    //return false;
    /*
    if(routeNam == 'user_list.index'){
        var listEventUrl = '?length='+listLength+'&search='+listSearch;
        var listRole = $('.listRole').val();
        if(parseInt(listRole) <= 0 || listRole == ''){
            listRole = '';
        }
        listEventUrl += '&role_code='+listRole;

        var listWc = $('.listWc').val();
        if(parseInt(listWc) <= 0 || listWc == ''){
            listWc = '';
        }
        listEventUrl += '&wc_code='+listWc;

        location = routeUrl+listEventUrl;
        //console.log(location); return false;
    }else if(routeNam == 'tagthem.index'){
        var listEventUrl = '?length='+listLength+'&search='+listSearch;
        var listType = $('.listType').val();
        if(listType == ''){
            listType = '';
        }
        listEventUrl += '&tag_type='+listType;
        location = routeUrl+listEventUrl;
    }else if(routeNam == 'special.districts'){
        location = routeUrl+"?length="+listLength+"&search="+listSearch;
    }else{
        //console.log(routeUrl+"/"+listLength+"/"+listSearch); return false;
        location = routeUrl+"/"+listLength+"/"+listSearch;
    }
    */
}

$('input').keydown(function(event) {
    if (event.which === 13) {
      var activeInputId = $(this).attr('data-id');
      if(activeInputId == 'btnSearchList'){
        $('.btnSearchList').trigger('click');
        event.preventDefault();
      }
    }
});
</script>

