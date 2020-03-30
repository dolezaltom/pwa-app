{*
 * @package Sp One Step Checkout
 * @version 1.0.2
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @copyright (c) 2016 YouTech Company. All Rights Reserved.
 * @author MagenTech http://www.magentech.com
 *}

{extends file='checkout/checkout.tpl'}

{block name='content'}
    <section id="content">
        <div id="spstepcheckout" class="js-current-step {if $register_customer}rc{/if}">
            <div class="spsco-hidden">
			<input type="hidden" id="logged" value="{$customer.is_logged|intval}" />
            <div class="spsco-loading-lg">
                <i class="fa fa-spin fa-spinner fa-4x"></i>
            </div>
			<div class="modal fade" id="spsco_modal" tabindex="-1" role="dialog"  aria-hidden="true">
				<div class="modal-dialog" role="document">
					<div class="modal-content">
						<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal" aria-label="Close">
								<span aria-hidden="true">&times;</span>
							</button>
							<h4 class="modal-title">New message</h4>
						</div>
						<div class="modal-body">
							<form>
							<div class="form-group">
							<label for="recipient-name" class="col-form-label">Recipient:</label>
							<input type="text" class="form-control" id="recipient-name">
							</div>
							<div class="form-group">
							<label for="message-text" class="col-form-label">Message:</label>
							<textarea class="form-control" id="message-text"></textarea>
							</div>
							</form>
						</div>
					</div>
				</div>
			</div>
			{if !$customer.is_logged}
				<div id="spsco_login_modal" class="modal fade" role="dialog" title="{l s='Login' mod='spstepcheckout'}">
					<div class="modal-dialog">
						<div class="modal-content">
							<div class="modal-header">
								<button type="button" class="close" data-dismiss="modal">&times;</button>
								<h4 class="modal-title">{l s='Login' mod='spstepcheckout'}</h4>
							</div>
							<div class="modal-body">
								<div class="loading_small"><i class="fa fa-spin fa-spinner fa-2x"></i></div>
								<div class="login-box">
									<form id="spsco_login_form" autocomplete="off">
										<div class="input-group">
											<span class="input-group-addon"><i class="fa fa-envelope-o fa-fw"></i></span>
											<input
												class="form-control txt-email"
												type="text"
												placeholder="{l s='E-mail' mod='spstepcheckout'}"
												data-validation="isEmail"
											/>
										</div>
										<br/>
										<div class="input-group">
											<span class="input-group-addon"><i class="fa fa-key fa-fw"></i></span>
											<input
												class="form-control txt-password"
												type="password"
												placeholder="{l s='Password' mod='spstepcheckout'}"
												data-validation="length"
												data-validation-length="min5"
											/>
										</div>
										<br/>
										<div class="alert alert-warning  hidden"></div>
										<br/>
										<button type="button"  class="btn btn-info btn-block btn-login">
											<i class="fa fa-lock fa-lg"></i>
											{l s='Login' mod='spstepcheckout'}
										</button>

										<p class="forget_password">
											<a href="{$urls.pages.password}">{l s='Forgot your password?' mod='spstepcheckout'}</a>
										</p>
									</form>
								</div>
							</div>	
						</div>	
					</div>	
				</div>
			{/if}
			</div>
            <div id="spsco_wrap" class="col-xs-12 col-12">
                <div class="row">
                    {foreach from=$position_steps item=column}
                        <div class="{$column.classes}">
                            <div class="row">
                                {foreach from=$column.rows item=row}
                                    {$spstepcheckout->includeTemplate('steps/'|cat:$row.name_step|cat:'.tpl', [register_customer => $register_customer, classes => $row.classes, 'CONFIGS' => $SPSCOVAR.CONFIGS, 'SPSCOVAR' => $SPSCOVAR]) nofilter}
                                {/foreach}
                            </div>
                        </div>
                    {/foreach}
                </div>
            </div>
            <div class="cf"></div>
        </div>
    </section>
{/block}