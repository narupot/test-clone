<div class="register-step-wrap">
	<ul>
		<li class="{{ $done_step>=1 ? 'complete':'' }} {{ $step=='register' ?' active':'' }}">
			<span class="step-icon"><i class="far fa-address-card"></i></span>
			<span class="step-name">@lang('auth.register')</span>
		</li>
		<li class="{{ $done_step>1 ? 'complete':'' }} {{ $step=='waiting_confirm' ?' active':'' }}">
			<span class="step-icon"><i class="fas fa-check"></i></span>
			<span class="step-name">@lang('auth.waiting_confirm')</span>
		</li>

		<li class="{{ $done_step>=3 ? 'complete':'' }} {{ $step=='shop_info' ?' active':'' }}">
			<span class="step-icon"><i class="fas fa-pencil-alt"></i></span>
			<span class="step-name">@lang('shop.shop_information')</span>
		</li>
		<li class="{{ $done_step>=4 ? 'complete':'' }} {{ $step=='bank_info' ?' active':'' }}">
			<span class="step-icon"><i class="fas fa-dollar-sign"></i></span>
			<span class="step-name">@lang('shop.bank_information')</span>
		</li>
		
		<li class="{{ $done_step>4 ? 'complete':'' }} {{ $step=='done' ?' active':'' }}">
			<span class="step-icon"><i class="far fa-handshake"></i></span>
			<span class="step-name">@lang('auth.done')</span>
		</li>
	</ul>

	<div class="top-title">							
			<h1 class="page-title" id="step_heading">{{ isset($step_heading)?$step_heading:'' }} </h1>										
	</div>
</div>