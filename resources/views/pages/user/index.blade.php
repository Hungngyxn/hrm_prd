@extends('layouts.admin', ['accesses' => $accesses, 'active' => 'user'])

@section('_content')
	<div class="container-fluid mt-2 px-4">
		<div class="row">
			<div class="col-12">
				<h4 class="font-weight-bold">Users</h4>
				<hr>
			</div>
		</div>

		<div class="row">
			<div class="col-12 mb-3">
				<div class="bg-light text-dark card p-3 overflow-auto">
					<div class="d-flex justify-content-between flex-wrap align-items-center">
						@if (collect($accesses)->where('menu_id', 4)->first()->status == 2)
							<a href="{{ route('user.create') }}" class="btn btn-outline-dark mb-3">
								<i class="fas fa-plus mr-1"></i>
								<span>Create</span>
							</a>
						@else
							<span class="mb-3"></span>
						@endif

						<div class="ms-auto">
							<form method="GET" action="{{ route('user.index') }}" id="filterForm"
								class="d-flex align-items-center gap-2 mb-3">
								<input type="text" name="search" value="{{ request('search') }}"
									class="form-control px-3 py-2" placeholder="Search ..." style="min-width: 250px;">

								<button type="submit" class="btn btn-outline-secondary px-4" id="btnsearch">
									<i class="fas fa-search"></i>
								</button>
								<button type="button" class="btn btn-danger" onclick="resetFilters()">
									<i class="fas fa-times"></i>
								</button>
							</form>
						</div>
					</div>


					@if (session('status'))
						<div class="alert alert-success">
							{{ session('status') }}
						</div>
					@endif
					<table class="table table-light table-striped table-hover table-bordered text-center">
						<thead>
							<tr>
								<th scope="col" class="table-dark">#</th>
								<th scope="col" class="table-dark">Name</th>
								<th scope="col" class="table-dark">Email</th>
								<th scope="col" class="table-dark">Role</th>
								<th scope="col" class="table-dark">Action</th>
							</tr>
						</thead>
						<tbody>
							@foreach ($users as $user)
								<tr>
									<th scope="row">{{ $loop->iteration + $users->firstItem() - 1 }}</th>
									<td class="w-25">
										@if($user->role->name !== 'Administrator')
											<a href="{{ route('user.show', ['user' => $user->id]) }}">{{ $user->name }}</a>
										@else
											<a>{{ $user->name }}</a>
										@endif
									</td>
									<td class="w-25">{{ $user->email }}</td>
									<td>
										<a>{{ $user->role->name }}</a>
									</td>
									<td>
										@if($user->role->name !== 'Administrator')
											{{-- Nút Edit --}}
											<a href="{{ route('user.edit', ['user' => $user->id]) }}"
												class="btn btn-sm btn-outline-primary">
												<i class="fas fa-edit"></i>
											</a>

											{{-- Nút Delete --}}
											<form action="{{ route('user.destroy', ['user' => $user->id]) }}" method="POST"
												style="display: inline-block;"
												onsubmit="return confirm('Bạn có chắc chắn muốn xóa người dùng này?');">
												@csrf
												@method('DELETE')
												<button type="submit" class="btn btn-sm btn-outline-danger">
													<i class="fas fa-trash"></i>
												</button>
											</form>
										@endif
									</td>
								</tr>
							@endforeach
						</tbody>
					</table>
					{{ $users->links() }}
				</div>
			</div>
		</div>
	</div>
	<script>
		function resetFilters() {
			const form = document.getElementById('filterForm');
			form.querySelector('input[name="search"]').value = '';
			document.getElementById('btnsearch').click();
		}
	</script>
@endsection