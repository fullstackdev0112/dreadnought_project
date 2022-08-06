<?php

namespace Webkul\Admin\Http\Controllers\Sales;

use Webkul\Admin\DataGrids\OrderShipmentsDataGrid;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\Sales\Repositories\OrderItemRepository;
use Webkul\Sales\Repositories\OrderRepository;
use Webkul\Sales\Repositories\ShipmentRepository;

class ShipmentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    protected $_config;

    /**
     * Create a new controller instance.
     *
     * @param  \Webkul\Sales\Repositories\ShipmentRepository   $shipmentRepository
     * @param  \Webkul\Sales\Repositories\OrderRepository  $orderRepository
     * @param  \Webkul\Sales\Repositories\OrderitemRepository  $orderItemRepository
     * @return void
     */
    public function __construct(
        protected ShipmentRepository $shipmentRepository,
        protected OrderRepository $orderRepository,
        protected OrderItemRepository $orderItemRepository
    )
    {
        $this->_config = request('_config');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        if (request()->ajax()) {
            return app(OrderShipmentsDataGrid::class)->toJson();
        }

        return view($this->_config['view']);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param  int  $orderId
     * @return \Illuminate\View\View
     */
    public function create($orderId)
    {
        $order = $this->orderRepository->findOrFail($orderId);

        if (! $order->channel || ! $order->canShip()) {
            session()->flash('error', trans('admin::app.sales.shipments.creation-error'));

            return redirect()->back();
        }

        return view($this->_config['view'], compact('order'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  int  $orderId
     * @return \Illuminate\Http\Response
     */
    public function store($orderId)
    {
        $order = $this->orderRepository->findOrFail($orderId);

        if (! $order->canShip()) {
            session()->flash('error', trans('admin::app.sales.shipments.order-error'));

            return redirect()->back();
        }

        $this->validate(request(), [
            'shipment.source'    => 'required',
            'shipment.items.*.*' => 'required|numeric|min:0',
        ]);

        if (! $this->isInventoryValidate(request()->all())) {
            session()->flash('error', trans('admin::app.sales.shipments.quantity-invalid'));

            return redirect()->back();
        }

        $this->shipmentRepository->create(array_merge(request()->all(), [
            'order_id' => $orderId,
        ]));

        session()->flash('success', trans('admin::app.response.create-success', ['name' => 'Shipment']));

        return redirect()->route($this->_config['redirect'], $orderId);
    }

    /**
     * Checks if requested quantity available or not.
     *
     * @param  array  $data
     * @return bool
     */
    public function isInventoryValidate(&$data)
    {
        if (! isset($data['shipment']['items'])) {
            return;
        }

        $valid = false;

        $inventorySourceId = $data['shipment']['source'];

        foreach ($data['shipment']['items'] as $itemId => $inventorySource) {
            $qty = $inventorySource[$inventorySourceId];

            if ((int) $qty) {
                $orderItem = $this->orderItemRepository->find($itemId);

                if ($orderItem->qty_to_ship < $qty) {
                    return false;
                }

                if ($orderItem->getTypeInstance()->isComposite()) {
                    foreach ($orderItem->children as $child) {
                        if (! $child->qty_ordered) {
                            continue;
                        }

                        $finalQty = ($child->qty_ordered / $orderItem->qty_ordered) * $qty;

                        $availableQty = $child->product->inventories()
                            ->where('inventory_source_id', $inventorySourceId)
                            ->sum('qty');

                        if (
                            $child->qty_to_ship < $finalQty
                            || $availableQty < $finalQty
                        ) {
                            return false;
                        }
                    }
                } else {
                    $availableQty = $orderItem->product->inventories()
                        ->where('inventory_source_id', $inventorySourceId)
                        ->sum('qty');

                    if (
                        $orderItem->qty_to_ship < $qty
                        || $availableQty < $qty
                    ) {
                        return false;
                    }
                }

                $valid = true;
            } else {
                unset($data['shipment']['items'][$itemId]);
            }
        }

        return $valid;
    }

    /**
     * Show the view for the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function view($id)
    {
        $shipment = $this->shipmentRepository->findOrFail($id);

        return view($this->_config['view'], compact('shipment'));
    }
}
