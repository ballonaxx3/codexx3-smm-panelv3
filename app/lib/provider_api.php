<?php
class ProviderAPI {
    public function addOrder($service, $link, $quantity) {
        return [
            'success' => true,
            'order_id' => rand(1000,9999)
        ];
    }

    public function getStatus($order_id) {
        return [
            'status' => 'completed'
        ];
    }
}
