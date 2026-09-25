@extends('_app')
@php
  $pageNm = 'Password Change';
@endphp
@section('title', $pageNm)
@section('content')

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Main content -->
    
      <!-- Default box -->
      <div class="card">
        <form action="{{ route('password.change') }}" method="post">
          @csrf
        <div class="card-header" style="background-color: #84363a; color:#fff;">
          <h3 class="card-title">{{ $pageNm }}</h3>
        </div>
        <div class="card-body">
          <div class="row from-group">
            <div class="col-sm-4">
              <label>Current Password</label>
              <div class="input-group">
                <div class="input-group-prepend">
                  <span class="input-group-text"><i class="fas fa-key"></i></span>
                </div>
                <input type="password" class="form-control current_password" name="current_password" placeholder=" Current password" required>
              </div>
            </div>
            <div class="col-sm-4">
              <label>New Password</label>
              <div class="input-group">
                <div class="input-group-prepend">
                  <span class="input-group-text"><i class="fas fa-key"></i></span>
                </div>
                <input type="password" class="form-control new_password" name="new_password" placeholder=" New password" required>
              </div>
            </div>
            <div class="col-sm-4">
              <label>Confirm Password</label>
              <div class="input-group">
                <div class="input-group-prepend">
                  <span class="input-group-text"><i class="fas fa-key"></i></span>
                </div>
                <input type="password" class="form-control confirm_password" name="confirm_password" placeholder=" Confirm password" required>
              </div>
            </div>
          </div>
        </div>
        <div class="card-footer">
          <button type="submit" class="btn btn-sm" style="background-color: #84363a; color:#fff;" name="submit"> Submit</button>
        </div>
        </form>
        <!-- /.card-footer-->
      </div>
      <!-- /.card -->

    </section>
    <!-- /.content -->
  </div>
  <!-- /.content-wrapper -->
@endsection
