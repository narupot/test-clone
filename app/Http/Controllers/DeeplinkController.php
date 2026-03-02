<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DeeplinkController extends Controller
{
    public function handleDeeplink(Request $request)
    {
        return view('deeplink');
    }

    // public function handleProductDeeplink($productId, $variantId)
    // {
    //     $appUrl = "simummuangonline://product/{$productId}/{$variantId}";
    //     $webUrl = "https://www.simummuangonline.com/product/{$productId}/{$variantId}";
    //     $fallbackUrl = "https://www.simummuangonline.com";

    //     // สำหรับ LINE และแพลตฟอร์มที่รองรับ Intent
    //     $intentUrl = "intent://product/{$productId}/{$variantId}#Intent;scheme=https;package=com.smm.buyer.smm_buyer;end";
    //     //intent://#Intent;scheme=https;package=com.smm.buyer.smm_buyer;end;

    //     return view('deeplink.product', compact('appUrl', 'webUrl', 'fallbackUrl', 'intentUrl'));
    // }

    public function handleProductDeeplink($category, $id)
    {
        // ส่งกลับ view พร้อมข้อมูลสินค้า
        return view('deeplink.product', [
            'category' => $category,
            'id' => $id
        ]);
    }

}
