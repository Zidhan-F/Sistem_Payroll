            <!-- Section: Simulasi Gaji -->
            <div id="viewSimulasi" class="view-section">
                <div class="content-card" style="max-width: 600px; margin: 0 auto;">
                    <div class="section-header" style="justify-content: center; flex-direction: column; text-align: center;">
                        <div style="background: rgba(52, 152, 219, 0.1); width: 80px; height: 80px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-bottom: 20px;">
                            <i class="fas fa-calculator" style="font-size: 32px; color: var(--info);"></i>
                        </div>
                        <h2 style="font-size: 24px; font-weight: 700; color: #2c3e50; margin-bottom: 10px;">Upload UMP/UMK</h2>
                        <p style="color: #666; font-size: 14px;">Hitung estimasi Take Home Pay berdasarkan UMP/UMK daerah</p>
                    </div>
                    
                    <div style="margin-top: 30px;">
                        <div class="form-group" style="margin-bottom: 20px;">
                            <label style="font-weight: 600; margin-bottom: 8px; display: block;">Tipe Daerah</label>
                            <select id="simulasiType" onchange="loadSimulasiRegions()" style="width: 100%; padding: 12px; border-radius: 10px; border: 1px solid #ddd; font-family: inherit;">
                                <option value="UMP">Provinsi (UMP)</option>
                                <option value="UMK">Kota/Kabupaten (UMK)</option>
                                <option value="NOMINAL">Nominal (Input Manual)</option>
                            </select>
                        </div>

                        <div class="form-group" style="margin-bottom: 20px;">
                            <label style="font-weight: 600; margin-bottom: 8px; display: block;">Pilih Daerah</label>
                            <select id="simulasiRegion" style="width: 100%; padding: 12px; border-radius: 10px; border: 1px solid #ddd; font-family: inherit;">
                                <option value="">-- Pilih Provinsi --</option>
                                <option value="p1">ACEH</option>
                                <option value="p2">SUMATERA UTARA</option>
                                <option value="p3">SUMATERA BARAT</option>
                                <option value="p4">RIAU</option>
                                <option value="p5">JAMBI</option>
                                <option value="p6">SUMATERA SELATAN</option>
                                <option value="p7">BENGKULU</option>
                                <option value="p8">LAMPUNG</option>
                                <option value="p9">KEP. BANGKA BELITUNG</option>
                                <option value="p10">KEPULAUAN RIAU</option>
                                <option value="p11">DKI JAKARTA</option>
                                <option value="p12">JAWA BARAT</option>
                                <option value="p13">JAWA TENGAH</option>
                                <option value="p14">DI YOGYAKARTA</option>
                                <option value="p15">JAWA TIMUR</option>
                                <option value="p16">BANTEN</option>
                                <option value="p17">BALI</option>
                                <option value="p18">NUSA TENGGARA BARAT</option>
                                <option value="p19">NUSA TENGGARA TIMUR</option>
                                <option value="p20">KALIMANTAN BARAT</option>
                                <option value="p21">KALIMANTAN TENGAH</option>
                                <option value="p22">KALIMANTAN SELATAN</option>
                                <option value="p23">KALIMANTAN TIMUR</option>
                                <option value="p24">KALIMANTAN UTARA</option>
                                <option value="p25">SULAWESI UTARA</option>
                                <option value="p26">SULAWESI TENGAH</option>
                                <option value="p27">SULAWESI SELATAN</option>
                                <option value="p28">SULAWESI TENGGARA</option>
                                <option value="p29">GORONTALO</option>
                                <option value="p30">SULAWESI BARAT</option>
                                <option value="p31">MALUKU</option>
                                <option value="p32">MALUKU UTARA</option>
                                <option value="p33">PAPUA</option>
                                <option value="p34">PAPUA BARAT</option>
                                <option value="p35">PAPUA SELATAN</option>
                                <option value="p36">PAPUA TENGAH</option>
                                <option value="p37">PAPUA PEGUNUNGAN</option>
                                <option value="p38">PAPUA BARAT DAYA</option>
                            </select>
                        </div>

                        <button class="btn-save" onclick="hitungSimulasiGaji()" style="width: 100%; padding: 15px; background: var(--primary-color); color: white; border-radius: 10px; font-weight: 600; cursor: pointer; border: none; transition: 0.3s; margin-top: 10px;">
                            <i class="fas fa-search-dollar" style="margin-right: 8px;"></i> Cek Estimasi Gaji
                        </button>

                        <div id="simulasiResult" style="display: none; margin-top: 30px; padding: 25px; background: #f8f9fa; border-radius: 16px; border: 1px solid #eee; animation: fadeIn 0.5s;">
                            <div style="display: flex; justify-content: space-between; margin-bottom: 12px;">
                                <span style="color: #666; font-size: 14px;">Gaji Pokok:</span>
                                <span id="simBasic" style="font-weight: 600; color: #2c3e50;">-</span>
                            </div>
                            <div style="display: flex; justify-content: space-between; margin-bottom: 12px;">
                                <span style="color: #666; font-size: 14px;">Tunjangan Tetap (10%):</span>
                                <span id="simAllowance" style="font-weight: 600; color: #2c3e50;">-</span>
                            </div>
                            <div style="display: flex; justify-content: space-between; padding-top: 20px; border-top: 2px dashed #ddd; margin-top: 20px;">
                                <span style="font-weight: 700; color: #2c3e50;">Total Estimasi THP:</span>
                                <span id="simTotal" style="font-weight: 800; color: #27ae60; font-size: 22px;">-</span>
                            </div>
                            <p style="font-size: 11px; color: #999; text-align: center; margin-top: 20px; line-height: 1.5;">
                                *Hasil simulasi ini hanya perkiraan. Nilai riil dapat berbeda tergantung kebijakan potongan BPJS, pajak, dan komponen lainnya.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
