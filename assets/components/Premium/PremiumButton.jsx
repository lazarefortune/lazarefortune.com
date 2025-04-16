import { useToggle } from '../../functions/hooks.js'
import { SlideIn } from '../SlideIn.jsx'
import React, { useEffect, useRef, useState } from 'react'
import { classNames } from '../../functions/dom.js'
import { vatPrice } from '../../functions/vat.js'
import { ApiError, jsonFetch } from '../../functions/api.js'
import { flash } from '../../elements/Alert.js'
import scriptjs from 'scriptjs'
import { CountrySelect } from '../CountrySelect'
import { importScript } from '../../functions/script.js'
import { isAuthenticated } from '../../functions/auth.js'

export function PremiumButton({ children, plan, price, duration, stripeKey, paypalId }) {
    const [payment, togglePayment] = useToggle(false)
    const description = `Compte premium ${duration} mois`

    if (!isAuthenticated()) {
        return (
            <a href="/connexion?redirect=/premium" className="btn btn-premium btn-premium-glow w-full">
                {children}
            </a>
        )
    }

    if (!payment) {
        return (
            <button className="btn btn-premium btn-premium-glow w-full" onClick={togglePayment}>
                {children}
            </button>
        )
    }

    return (
        <SlideIn show={true}>
            <div className="p-6 bg-gray-50 dark:bg-primary-950 rounded-xl">
                <PaymentMethods
                    plan={plan}
                    price={price}
                    description={description}
                    stripeKey={stripeKey}
                    paypalId={paypalId}
                />
            </div>
        </SlideIn>
    )
}

const PAYMENT_CARD = 'CARD'
const PAYMENT_PAYPAL = 'PAYPAL'

function PaymentMethods({ plan, onPaypalApproval, description, price, stripeKey, paypalId }) {
    const [method, setMethod] = useState(PAYMENT_CARD)

    return (
        <div className="text-left space-y-4">
            <div className="form-group">
                <label className="label">Méthode de paiement</label>
                <div className="flex space-x-2">
                    <button
                        onClick={() => setMethod(PAYMENT_CARD)}
                        className={classNames(method === PAYMENT_CARD ? 'btn btn-primary' : 'btn btn-light')}
                    >
                        <img src="/images/payment-methods.png" width="76" className="mr-1" alt="Carte bancaire"/>
                    </button>
                    <button
                        onClick={() => setMethod(PAYMENT_PAYPAL)}
                        className={classNames(method === PAYMENT_PAYPAL ? 'btn btn-primary' : 'btn btn-light')}
                    >
                        <img src="/images/paypal.svg" width="20" className="mr-1" alt="PayPal"/>
                    </button>
                </div>
            </div>
            {method === PAYMENT_PAYPAL ? (
                <PaymentPaypal
                    planId={plan}
                    price={price}
                    description={description}
                    onApprove={onPaypalApproval}
                    paypalId={paypalId}
                />
            ) : (
                <PaymentCard plan={plan} publicKey={stripeKey}/>
            )}
        </div>
    )
}

function PaymentPaypal({ planId, price, description, paypalId }) {
    const container = useRef(null)
    const approveRef = useRef(null)
    const currency = 'EUR'
    const [country, setCountry] = useState(null)
    const [loading, toggleLoading] = useToggle(false)
    const vat = country ? vatPrice(price, country) : null

    // Lorsqu'un paiement est approuvé par PayPal
    approveRef.current = async orderId => {
        toggleLoading()
        try {
            await jsonFetch(`/api/premium/paypal/${orderId}`, { method: 'POST' })
            await redirect('?success=1')
        } catch (e) {
            if (e instanceof ApiError) {
                flash(e.name, 'danger', null)
            }
        }
        toggleLoading()
    }

    useEffect(() => {
        if (vat === null) return
        toggleLoading()
        const priceWithoutTax = price - vat
        scriptjs(
            `https://www.paypal.com/sdk/js?client-id=${paypalId}&disable-funding=card,credit&integration-date=2020-12-10&currency=${currency}`,
            () => {
                toggleLoading()
                container.current.innerHTML = ''
                window.paypal
                    .Buttons({
                        style: { label: 'pay' },
                        createOrder: (data, actions) => {
                            return actions.order.create({
                                purchase_units: [
                                    {
                                        description,
                                        custom_id: planId,
                                        items: [
                                            {
                                                name: description,
                                                quantity: '1',
                                                unit_amount: { value: priceWithoutTax, currency_code: currency },
                                                tax: { value: vat, currency_code: currency },
                                                category: 'DIGITAL_GOODS'
                                            }
                                        ],
                                        amount: {
                                            currency_code: currency,
                                            value: price,
                                            breakdown: {
                                                item_total: { currency_code: currency, value: priceWithoutTax },
                                                tax_total: { currency_code: currency, value: vat }
                                            }
                                        }
                                    }
                                ]
                            })
                        },
                        onApprove: data => {
                            approveRef.current(data.orderID)
                        }
                    })
                    .render(container.current)
                return () => {
                    container.current.innerHTML = ''
                }
            }
        )
    }, [description, planId, price, vat, paypalId])

    return (
        <div className="space-y-4 max-w-full overflow-hidden">
            <div className="form-group">
                <label htmlFor="countryCode" className="label">
                    Pays de résidence
                </label>
                <CountrySelect
                    id="countryCode"
                    value={country}
                    onChange={e => setCountry(e.target.value)}
                    required
                    className="form-select w-full max-w-xs"
                />
            </div>
            {country && (
                <div
                    ref={container}
                    className="min-h-[52px]"
                    style={{
                        display: loading ? 'none' : undefined,
                        maxWidth: '100%',
                        overflow: 'hidden'
                    }}
                />
            )}
            {loading && (
                <button className="btn btn-disabled w-full">
                    Chargement...
                </button>
            )}
        </div>
    )
}

function PaymentCard({ plan, publicKey }) {
    const [subscription, toggleSubscription] = useToggle(true)
    const [loading, toggleLoading] = useToggle(false)

    const startPayment = async () => {
        toggleLoading()
        try {
            const Stripe = await importScript('https://js.stripe.com/v3/', 'Stripe')
            const stripe = new Stripe(publicKey)
            const { id } = await jsonFetch(
                `/api/premium/${plan}/stripe/checkout?subscription=${subscription ? '1' : '0'}`,
                { method: 'POST' }
            )
            stripe.redirectToCheckout({ sessionId: id })
        } catch (e) {
            flash(e instanceof ApiError ? e.name : e, 'error')
            toggleLoading()
        }
    }

    return (
        <div className="space-y-4">
            <div className="flex items-center space-x-2">
                <input
                    type="checkbox"
                    id={`subscription${plan}`}
                    name="subscription"
                    checked={subscription}
                    onChange={toggleSubscription}
                    className="form-checkbox"
                />
                <label htmlFor={`subscription${plan}`} className="label">
                    Renouveler automatiquement
                </label>
            </div>
            {subscription && (
                <p className="text-sm text-slate-500 dark:text-slate-300">
                    Le renouvellement automatique est activé, vous serez prélevé automatiquement à la fin de chaque période.
                    Vous pourrez interrompre l'abonnement à tout moment depuis{' '}
                    <a href="/mon-compte" target="_blank" rel="noopener noreferrer" className="link">
                        votre compte
                    </a>.
                </p>
            )}
            <button className="btn btn-premium btn-premium-glow w-full" onClick={startPayment} disabled={loading}>
                {loading ? 'Chargement...' : (
                    <>
                        {subscription ? "S'abonner via" : 'Payer via'}{' '}
                        <img src="/images/stripe.svg"  className="ml-2 h-4" alt="Stripe" />
                    </>
                )}
            </button>
        </div>
    )
}
