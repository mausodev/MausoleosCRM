function aplicarDescuento(descuentoId, ventaReal) {
    let descuento = obtenerDescuentoPorId(descuentoId); // Función que obtiene el descuento por ID
    if (descuento) {
        let montoDescuento = (descuento / 100) * ventaReal; // Calcula el monto del descuento
        let ventaConDescuento = ventaReal - montoDescuento; // Aplica el descuento a la venta real
        return ventaConDescuento; // Retorna el nuevo monto de la venta
    } else {
        throw new Error("Descuento no encontrado");
    }
}
